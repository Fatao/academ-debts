<?php


namespace App\Http\Controllers;


use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\{Debt, Retake, RetakeChangeRequest, TeacherRoleRequest, Notification, User};


class TeacherController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $disciplineIds = $user->disciplines()->pluck('disciplines.id');


        $openDebts   = Debt::whereIn('discipline_id', $disciplineIds)->where('status', 'DEBT')->count();
        $closedDebts = Debt::whereIn('discipline_id', $disciplineIds)->where('status', 'CLOSED')->count();
        $retakes     = $user->retakesAsTeacher()->where('status', 'SCHEDULED')->count();


        $recentDebts = Debt::whereIn('discipline_id', $disciplineIds)
            ->with('student', 'discipline')
            ->latest()->take(5)->get();


        return view('teacher.dashboard', compact(
            'user', 'openDebts', 'closedDebts', 'retakes', 'recentDebts'
        ));
    }


    public function debts()
    {
        $user = Auth::user();
        $disciplineIds = $user->disciplines()->pluck('disciplines.id');


        $debts = Debt::whereIn('discipline_id', $disciplineIds)
            ->with('student', 'discipline', 'assignedBy')
            ->latest()->get();


        return view('teacher.debts', compact('debts'));
    }


    public function createDebt()
    {
        $user        = Auth::user();
        $disciplines = $user->disciplines;
        $students    = User::where('is_teacher', false)
                           ->where('is_dean', false)
                           ->where('is_admin', false)
                           ->with('group')->orderBy('last_name')->get();

        return view('teacher.create-debt', compact('disciplines', 'students'));
    }


    public function storeDebt(Request $request)
    {
        $request->validate([
            'student_id'    => ['required', 'exists:users,id'],
            'discipline_id' => ['required', 'exists:disciplines,id'],
            'comment'       => ['nullable', 'string', 'max:500'],
        ], [
            'student_id.required'    => 'Выберите студента.',
            'discipline_id.required' => 'Выберите дисциплину.',
        ]);

        $user = Auth::user();
        $disciplineIds = $user->disciplines()->pluck('disciplines.id');

        if (!$disciplineIds->contains($request->discipline_id)) {
            abort(403);
        }

        $debt = Debt::create([
            'student_id'     => $request->student_id,
            'discipline_id'  => $request->discipline_id,
            'assigned_by_id' => $user->id,
            'status'         => 'DEBT',
            'comment'        => $request->comment,
        ]);

        Notification::send(
            $request->student_id,
            Notification::TYPE_DEBT_CREATED,
            'Новая задолженность',
            "Вам выставлена задолженность по дисциплине «{$debt->discipline->name}».",
            ['related_debt_id' => $debt->id]
        );

        return redirect()->route('teacher.debts')->with('success', 'Задолженность выставлена.');
    }


    public function closeDebt(Request $request, Debt $debt)
    {
        $user = Auth::user();


        // Проверка — преподаватель ведёт эту дисциплину
        $disciplineIds = $user->disciplines()->pluck('disciplines.id');
        if (!$disciplineIds->contains($debt->discipline_id)) {
            abort(403);
        }


        $request->validate([
            'grade_value' => ['required', 'numeric', 'min:0', 'max:100'],
            'grade_scale' => ['required', 'in:0_5,0_100,PASS_FAIL'],
        ], [
            'grade_value.required' => 'Введите оценку.',
            'grade_value.numeric'  => 'Оценка должна быть числом.',
            'grade_scale.required' => 'Выберите шкалу оценивания.',
        ]);


        $debt->close($user, $request->grade_value, $request->grade_scale);


        return back()->with('success', 'Задолженность закрыта. Оценка выставлена.');
    }


    public function retakes()
    {
        $retakes = Auth::user()->retakesAsTeacher()
            ->with('discipline', 'students')
            ->orderByDesc('start_datetime')
            ->get();


        foreach ($retakes as $retake) {
            $retake->syncStatus();
        }


        return view('teacher.retakes', compact('retakes'));
    }


    public function retakeResults(\App\Models\Retake $retake)
    {
        $user = Auth::user();

        if (!$retake->teachers->contains($user->id)) {
            abort(403);
        }

        $retake->load('discipline', 'students.group');
        return view('teacher.retake-results', compact('retake'));
    }


    public function saveRetakeResults(Request $request, \App\Models\Retake $retake)
    {
        $user = Auth::user();

        if (!$retake->teachers->contains($user->id)) {
            abort(403);
        }

        $request->validate([
            'results'               => ['required', 'array'],
            'results.*.result_status' => ['required', 'in:PASSED,FAILED,NOT_TAKEN'],
            'results.*.grade_value' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'results.*.grade_scale' => ['nullable', 'in:0_5,0_100,PASS_FAIL'],
        ]);

        foreach ($request->results as $studentId => $data) {
            $retake->students()->updateExistingPivot($studentId, [
                'result_status' => $data['result_status'],
                'grade_value'   => $data['grade_value'] ?? null,
                'grade_scale'   => $data['grade_scale'] ?? null,
                'updated_by_id' => $user->id,
                'updated_at'    => now(),
            ]);

            // Автозакрытие долга если сдал
            if ($data['result_status'] === 'PASSED') {
                $debt = Debt::where('student_id', $studentId)
                    ->where('discipline_id', $retake->discipline_id)
                    ->where('status', 'DEBT')
                    ->first();

                if ($debt) {
                    $debt->close($user, $data['grade_value'] ?? null, $data['grade_scale'] ?? null);
                }
            }
        }

        return back()->with('success', 'Результаты пересдачи сохранены.');
    }


    public function requests()
    {
        $requests = RetakeChangeRequest::where('requested_by_id', Auth::id())
            ->with('retake.discipline')
            ->latest()->get();


        $retakes = Auth::user()->retakesAsTeacher()
            ->where('status', '!=', 'COMPLETED')
            ->with('discipline')->get();


        return view('teacher.requests', compact('requests', 'retakes'));
    }


    public function storeRequest(Request $request)
    {
        $request->validate([
            'retake_id'          => ['required', 'exists:retakes,id'],
            'new_building'       => ['nullable', 'string', 'max:20'],
            'new_room'           => ['nullable', 'string', 'max:20'],
            'new_start_datetime' => ['nullable', 'date'],
            'new_duration_minutes' => ['nullable', 'integer', 'min:15'],
            'comment'            => ['required', 'string', 'max:1000'],
        ], [
            'retake_id.required' => 'Выберите пересдачу.',
            'comment.required'   => 'Укажите причину заявки.',
        ]);


        RetakeChangeRequest::create([
            'retake_id'            => $request->retake_id,
            'requested_by_id'      => Auth::id(),
            'new_building'         => $request->new_building,
            'new_room'             => $request->new_room,
            'new_start_datetime'   => $request->new_start_datetime,
            'new_duration_minutes' => $request->new_duration_minutes,
            'comment'              => $request->comment,
            'status'               => 'PENDING',
        ]);


        return back()->with('success', 'Заявка подана. Ожидайте решения деканата.');
    }
}