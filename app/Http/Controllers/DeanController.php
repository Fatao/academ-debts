<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\{Debt, Retake, User, Discipline, Group, RetakeChangeRequest, Notification};
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeanController extends Controller
{
    public function dashboard()
    {
        $totalDebts    = Debt::where('status', 'DEBT')->count();
        $closedDebts   = Debt::where('status', 'CLOSED')->count();
        $totalRetakes  = Retake::count();
        $pendingRequests = RetakeChangeRequest::where('status', 'PENDING')->count();

        $recentDebts = Debt::with('student', 'discipline', 'assignedBy')
            ->latest()->take(8)->get();

        return view('dean.dashboard', compact(
            'totalDebts', 'closedDebts', 'totalRetakes',
            'pendingRequests', 'recentDebts'
        ));
    }

    public function debts()
    {
        $debts = Debt::with('student.group', 'discipline', 'assignedBy')
            ->latest()->get();

        $groups     = Group::orderBy('name')->get();
        $disciplines = Discipline::orderBy('name')->get();

        return view('dean.debts', compact('debts', 'groups', 'disciplines'));
    }

    public function retakes()
    {
        $retakes = Retake::with('discipline', 'teachers', 'students', 'createdBy')
            ->orderByDesc('start_datetime')->get();

        foreach ($retakes as $r) { $r->syncStatus(); }

        return view('dean.retakes.index', compact('retakes'));
    }




    public function createRetake()
   {
    $disciplines  = Discipline::orderBy('name')->get();
    $teachers     = User::where('is_teacher', true)->orderBy('last_name')->get();
    $students     = User::where('is_teacher', false)
                        ->where('is_dean', false)
                        ->where('is_admin', false)
                        ->with('group')
                        ->orderBy('last_name')
                        ->get();
    $groupsByYear = \App\Models\Group::orderBy('name')->get()->groupBy('year');

    return view('dean.retakes.create', compact(
        'disciplines', 'teachers', 'students', 'groupsByYear'
    ));
    }
    


    public function storeRetake(Request $request)
    {
        $request->validate([
            'discipline_id'    => ['required', 'exists:disciplines,id'],
            'type'             => ['required', 'in:REGULAR,COMMISSION'],
            'building_number'  => ['required', 'string', 'max:20'],
            'room_number'      => ['required', 'string', 'max:20'],
            'start_datetime'   => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'min:15'],
            'teacher_ids'      => ['required', 'array', 'min:1'],
            'teacher_ids.*'    => ['exists:users,id'],
            'student_ids'      => ['required', 'array', 'min:1'],
            'student_ids.*'    => ['exists:users,id'],
        ], [
            'discipline_id.required'   => 'Выберите дисциплину.',
            'type.required'            => 'Выберите тип пересдачи.',
            'building_number.required' => 'Укажите номер корпуса.',
            'room_number.required'     => 'Укажите номер аудитории.',
            'start_datetime.required'  => 'Укажите дату и время.',
            'start_datetime.after'     => 'Дата должна быть в будущем.',
            'duration_minutes.required'=> 'Укажите продолжительность.',
            'teacher_ids.required'     => 'Выберите хотя бы одного преподавателя.',
            'student_ids.required'     => 'Выберите хотя бы одного студента.',
        ]);

        if ($request->type === 'COMMISSION' && count($request->teacher_ids) < 3) {
            return back()->withErrors(['teacher_ids' => 'При пересдаче с комиссией необходимо назначить минимум 3 преподавателей.'])->withInput();
        }

        $retake = Retake::create([
            'discipline_id'    => $request->discipline_id,
            'type'             => $request->type,
            'building_number'  => $request->building_number,
            'room_number'      => $request->room_number,
            'start_datetime'   => $request->start_datetime,
            'duration_minutes' => $request->duration_minutes,
            'status'           => 'SCHEDULED',
            'created_by_id'    => Auth::id(),
        ]);

        $retake->teachers()->attach($request->teacher_ids);

        $studentData = collect($request->student_ids)->mapWithKeys(fn($id) => [
            $id => ['result_status' => 'NOT_TAKEN']
        ])->toArray();
        $retake->students()->attach($studentData);

        $discipline = Discipline::find($request->discipline_id);
        $dateStr = \Carbon\Carbon::parse($request->start_datetime)->format('d.m.Y в H:i');

        foreach ($request->student_ids as $sid) {
            Notification::send($sid, Notification::TYPE_RETAKE_ASSIGNED,
                'Назначена пересдача',
                "Вам назначена пересдача по дисциплине «{$discipline->name}» на {$dateStr}. Место: корп. {$request->building_number}, ауд. {$request->room_number}.",
                ['related_retake_id' => $retake->id]
            );
        }

        foreach ($request->teacher_ids as $tid) {
            Notification::send($tid, Notification::TYPE_RETAKE_ASSIGNED,
                'Назначена пересдача',
                "Вы назначены на пересдачу по дисциплине «{$discipline->name}» на {$dateStr}.",
                ['related_retake_id' => $retake->id]
            );
        }

        return redirect()->route('dean.retakes.index')->with('success', 'Пересдача успешно назначена.');
    }

    public function requests()
    {
        $requests = RetakeChangeRequest::with('retake.discipline', 'requestedBy')
            ->orderByRaw("FIELD(status, 'PENDING', 'APPROVED', 'REJECTED')")
            ->latest()->get();

        return view('dean.requests', compact('requests'));
    }

    public function reviewRequest(Request $request, RetakeChangeRequest $changeRequest)
    {
        $request->validate([
            'decision'     => ['required', 'in:APPROVED,REJECTED'],
            'dean_comment' => ['required_if:decision,REJECTED', 'nullable', 'string', 'max:500'],
        ], [
            'decision.required'          => 'Выберите решение.',
            'dean_comment.required_if'   => 'При отклонении необходимо указать причину.',
        ]);

        $changeRequest->update([
            'status'        => $request->decision,
            'dean_comment'  => $request->dean_comment,
            'reviewed_by_id'=> Auth::id(),
            'reviewed_at'   => now(),
        ]);

        if ($request->decision === 'APPROVED') {
            $updates = array_filter([
                'building_number'  => $changeRequest->new_building,
                'room_number'      => $changeRequest->new_room,
                'start_datetime'   => $changeRequest->new_start_datetime,
                'duration_minutes' => $changeRequest->new_duration_minutes,
            ]);
            if ($updates) {
                $changeRequest->retake->update($updates);
            }

            $notifType = Notification::TYPE_REQUEST_APPROVED;
            $notifMsg  = "Ваша заявка на изменение пересдачи была одобрена.";
        } else {
            $notifType = Notification::TYPE_REQUEST_REJECTED;
            $notifMsg  = "Ваша заявка на изменение пересдачи была отклонена. Причина: {$request->dean_comment}";
        }

        Notification::send($changeRequest->requested_by_id, $notifType,
            $request->decision === 'APPROVED' ? 'Заявка одобрена' : 'Заявка отклонена',
            $notifMsg,
            ['related_retake_id' => $changeRequest->retake_id]
        );

        return back()->with('success', 'Решение по заявке сохранено.');
    }

    public function reports()
    {
        return view('dean.reports');
    }

    public function exportCsv(Request $request)
    {
        $from = $request->from ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $request->to   ?? now()->format('Y-m-d');

        $retakes = Retake::with('discipline', 'students')
            ->whereBetween('start_datetime', [$from, $to . ' 23:59:59'])
            ->where('status', 'COMPLETED')
            ->get();

        $response = new StreamedResponse(function () use ($retakes) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel
            fputcsv($handle, ['Дисциплина', 'Дата', 'Тип', 'Студентов', 'Сдали', 'Не сдали', 'Средняя оценка'], ';');

            foreach ($retakes as $retake) {
                $students = $retake->students;
                $passed   = $students->where('pivot.result_status', 'PASSED')->count();
                $failed   = $students->where('pivot.result_status', 'FAILED')->count();
                $grades   = $students->whereNotNull('pivot.grade_value')->pluck('pivot.grade_value');
                $avg      = $grades->count() ? number_format($grades->average(), 2, ',', '') : '—';

                fputcsv($handle, [
                    $retake->discipline->name,
                    $retake->start_datetime->format('d.m.Y'),
                    $retake->typeLabel(),
                    $students->count(),
                    $passed,
                    $failed,
                    $avg,
                ], ';');
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="pересдачи_' . $from . '_' . $to . '.csv"');

        return $response;
    }
}