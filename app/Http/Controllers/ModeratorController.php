<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\{Debt, Retake, User, Discipline, Group, RetakeChangeRequest, Notification};
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ModeratorController extends Controller
{
    public function dashboard()
    {
        $totalDebts    = Debt::where('status', 'DEBT')->count();
        $closedDebts   = Debt::where('status', 'CLOSED')->count();
        $totalRetakes  = Retake::count();
        $pendingRequests = RetakeChangeRequest::where('status', 'PENDING')->count();

        $recentDebts = Debt::with('freelancer', 'discipline', 'assignedBy')
            ->latest()->take(8)->get();

        return view('moderator.dashboard', compact(
            'totalDebts', 'closedDebts', 'totalRetakes',
            'pendingRequests', 'recentDebts'
        ));
    }

    public function debts()
    {
        $debts = Debt::with('freelancer.group', 'discipline', 'assignedBy')
            ->latest()->get();

        $groups     = Group::orderBy('name')->get();
        $disciplines = Discipline::orderBy('name')->get();

        return view('moderator.debts', compact('debts', 'groups', 'disciplines'));
    }

    public function retakes()
    {
        $retakes = Retake::with('discipline', 'jobgivers', 'freelancers', 'createdBy')
            ->orderByDesc('start_datetime')->get();

        foreach ($retakes as $r) { $r->syncStatus(); }

        return view('moderator.retakes.index', compact('retakes'));
    }




    public function createRetake()
   {
    $disciplines  = Discipline::orderBy('name')->get();
    $jobgivers     = User::where('is_jobgiver', true)->orderBy('last_name')->get();
    $freelancers     = User::where('is_jobgiver', false)
                        ->where('is_moderator', false)
                        ->where('is_admin', false)
                        ->with('group')
                        ->orderBy('last_name')
                        ->get();
    $groupsByYear = \App\Models\Group::orderBy('name')->get()->groupBy('year');

    return view('moderator.retakes.create', compact(
        'disciplines', 'jobgivers', 'freelancers', 'groupsByYear'
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
            'jobgiver_ids'      => ['required', 'array', 'min:1'],
            'jobgiver_ids.*'    => ['exists:users,id'],
            'freelancer_ids'      => ['required', 'array', 'min:1'],
            'freelancer_ids.*'    => ['exists:users,id'],
        ], [
            'discipline_id.required'   => 'Выберите заказ.',
            'type.required'            => 'Выберите тип.',
            'building_number.required' => 'Укажите номер корпуса(надо?).',
            'room_number.required'     => 'Укажите номер аудитории(надо?).',
            'start_datetime.required'  => 'Укажите дату и время дедлайна.',
            'start_datetime.after'     => 'Дата должна быть в будущем.',
            'duration_minutes.required'=> 'Укажите продолжительность.',
            'jobgiver_ids.required'     => 'Выберите хотя бы одного заказчика.',
            'freelancer_ids.required'     => 'Выберите хотя бы одного фрилансера.',
        ]);

        if ($request->type === 'COMMISSION' && count($request->jobgiver_ids) < 3) {
            return back()->withErrors(['jobgiver_ids' => 'При пересдаче с комиссией необходимо назначить минимум 3 преподавателей.(надо?)'])->withInput();
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

        $retake->jobgivers()->attach($request->jobgiver_ids);

        $freelancerData = collect($request->freelancer_ids)->mapWithKeys(fn($id) => [
            $id => ['result_status' => 'NOT_TAKEN']
        ])->toArray();
        $retake->freelancers()->attach($freelancerData);

        $discipline = Discipline::find($request->discipline_id);
        $dateStr = \Carbon\Carbon::parse($request->start_datetime)->format('d.m.Y в H:i');

        foreach ($request->freelancer_ids as $sid) {
            Notification::send($sid, Notification::TYPE_RETAKE_ASSIGNED,
                'Назначена пересдача',
                "Вам назначена пересдача по дисциплине «{$discipline->name}» на {$dateStr}. Место: корп. {$request->building_number}, ауд. {$request->room_number}.",
                ['related_retake_id' => $retake->id]
            );
        }

        foreach ($request->jobgiver_ids as $tid) {
            Notification::send($tid, Notification::TYPE_RETAKE_ASSIGNED,
                'Назначена пересдача',
                "Вы назначены на пересдачу по дисциплине «{$discipline->name}» на {$dateStr}.",
                ['related_retake_id' => $retake->id]
            );
        }

        return redirect()->route('moderator.retakes.index')->with('success', 'Пересдача успешно назначена.');
    }

    public function requests()
    {
        $requests = RetakeChangeRequest::with('retake.discipline', 'requestedBy')
            ->orderByRaw("FIELD(status, 'PENDING', 'APPROVED', 'REJECTED')")
            ->latest()->get();

        return view('moderator.requests', compact('requests'));
    }

    public function reviewRequest(Request $request, RetakeChangeRequest $changeRequest)
    {
        $request->validate([
            'decision'     => ['required', 'in:APPROVED,REJECTED'],
            'moderator_comment' => ['required_if:decision,REJECTED', 'nullable', 'string', 'max:500'],
        ], [
            'decision.required'          => 'Выберите решение.',
            'moderator_comment.required_if'   => 'При отклонении необходимо указать причину.',
        ]);

        $changeRequest->update([
            'status'        => $request->decision,
            'moderator_comment'  => $request->moderator_comment,
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
            $notifMsg  = "Ваша заявка на изменение пересдачи была отклонена. Причина: {$request->moderator_comment}";
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
        return view('moderator.reports');
    }

    public function exportCsv(Request $request)
    {
        $from = $request->from ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $request->to   ?? now()->format('Y-m-d');

        $retakes = Retake::with('discipline', 'freelancers')
            ->whereBetween('start_datetime', [$from, $to . ' 23:59:59'])
            ->where('status', 'COMPLETED')
            ->get();

        $response = new StreamedResponse(function () use ($retakes) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel
            fputcsv($handle, ['Дисциплина', 'Дата', 'Тип', 'Студентов', 'Сдали', 'Не сдали', 'Средняя оценка'], ';');

            foreach ($retakes as $retake) {
                $freelancers = $retake->freelancers;
                $passed   = $freelancers->where('pivot.result_status', 'PASSED')->count();
                $failed   = $freelancers->where('pivot.result_status', 'FAILED')->count();
                $grades   = $freelancers->whereNotNull('pivot.grade_value')->pluck('pivot.grade_value');
                $avg      = $grades->count() ? number_format($grades->average(), 2, ',', '') : '—';

                fputcsv($handle, [
                    $retake->discipline->name,
                    $retake->start_datetime->format('d.m.Y'),
                    $retake->typeLabel(),
                    $freelancers->count(),
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
