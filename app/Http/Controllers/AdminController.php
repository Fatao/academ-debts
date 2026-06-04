<?php

namespace App\Http\Controllers;

use App\Models\{User, Discipline, Group, TeacherRoleRequest, Notification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function users()
    {
        $users = User::with('group')->orderBy('last_name')->get();
        return view('admin.users', compact('users'));
    }

    public function toggleDean(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Нельзя изменить роль администратора.');
        }
        $user->update(['is_dean' => !$user->is_dean]);
        return back()->with('success', 'Роль деканата обновлена.');
    }

    public function toggleTeacher(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Нельзя изменить роль администратора.');
        }
        $user->update(['is_teacher' => !$user->is_teacher]);
        return back()->with('success', 'Роль преподавателя обновлена.');
    }

    public function disciplines()
    {
        $disciplines = Discipline::withCount('teachers', 'debts')->orderBy('name')->get();
        $teachers    = User::where('is_teacher', true)->orderBy('last_name')->get();
        return view('admin.disciplines', compact('disciplines', 'teachers'));
    }

    public function storeDiscipline(Request $request)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:50'],
            'teacher_ids' => ['nullable', 'array'],
        ], [
            'name.required' => 'Введите название дисциплины.',
        ]);

        $discipline = Discipline::create([
            'name' => $request->name,
            'code' => $request->code,
        ]);

        if ($request->teacher_ids) {
            $discipline->teachers()->attach($request->teacher_ids);
        }

        return back()->with('success', "Дисциплина «{$discipline->name}» добавлена.");
    }

    public function deleteDiscipline(Discipline $discipline)
    {
        if ($discipline->debts()->exists()) {
            return back()->with('error', 'Нельзя удалить дисциплину с задолженностями.');
        }
        $discipline->delete();
        return back()->with('success', 'Дисциплина удалена.');
    }

    public function roleRequests()
    {
        $requests = TeacherRoleRequest::with('user')
            ->orderByRaw("FIELD(status,'PENDING','APPROVED','REJECTED')")
            ->latest()->get();
        return view('admin.role-requests', compact('requests'));
    }

    public function reviewRoleRequest(Request $request, TeacherRoleRequest $roleRequest)
    {
        $request->validate([
            'decision'     => ['required', 'in:APPROVED,REJECTED'],
            'dean_comment' => ['required_if:decision,REJECTED', 'nullable', 'string'],
        ], [
            'decision.required'        => 'Выберите решение.',
            'dean_comment.required_if' => 'При отклонении укажите причину.',
        ]);

        $roleRequest->update([
            'status'         => $request->decision,
            'dean_comment'   => $request->dean_comment,
            'reviewed_by_id' => auth()->id(),
            'reviewed_at'    => now(),
        ]);

        if ($request->decision === 'APPROVED') {
            $roleRequest->user->update(['is_teacher' => true]);
            Notification::send(
                $roleRequest->user_id,
                Notification::TYPE_REQUEST_APPROVED,
                'Заявка одобрена',
                'Ваша заявка на получение роли «Преподаватель» была одобрена.'
            );
        } else {
            Notification::send(
                $roleRequest->user_id,
                Notification::TYPE_REQUEST_REJECTED,
                'Заявка отклонена',
                'Ваша заявка на получение роли «Преподаватель» была отклонена. Причина: ' . $request->dean_comment
            );
        }

        return back()->with('success', 'Решение сохранено.');
    }
}