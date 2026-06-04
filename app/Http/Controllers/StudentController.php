<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function dashboard()
    {
        $user   = Auth::user();
        $debts  = $user->debts()->with('discipline', 'assignedBy')->latest()->get();
        $retakes = $user->retakesAsStudent()->with('discipline')->orderByDesc('start_datetime')->get();

        $totalDebts  = $debts->where('status', 'DEBT')->count();
        $closedDebts = $debts->where('status', 'CLOSED')->count();
        $upcomingRetakes = $retakes->where('status', 'SCHEDULED')->count();

        return view('student.dashboard', compact(
            'user', 'debts', 'retakes',
            'totalDebts', 'closedDebts', 'upcomingRetakes'
        ));
    }

    public function debts()
    {
        $debts = Auth::user()->debts()->with('discipline', 'assignedBy')->latest()->get();
        return view('student.debts', compact('debts'));
    }

    public function retakes()
    {
        $retakes = Auth::user()->retakesAsStudent()
            ->with('discipline', 'teachers')
            ->orderByDesc('start_datetime')
            ->get();

        foreach ($retakes as $retake) {
            $retake->syncStatus();
        }

        return view('student.retakes', compact('retakes'));
    }

    public function requestTeacherRole()
{
    $existing = \App\Models\TeacherRoleRequest::where('user_id', Auth::id())
        ->whereIn('status', ['PENDING', 'APPROVED'])
        ->first();

    return view('student.request-role', compact('existing'));
}

public function submitTeacherRoleRequest(\Illuminate\Http\Request $request)
{
    $existing = \App\Models\TeacherRoleRequest::where('user_id', Auth::id())
        ->whereIn('status', ['PENDING', 'APPROVED'])
        ->first();

    if ($existing) {
        return back()->with('error', 'У вас уже есть активная заявка.');
    }

    $request->validate([
        'comment' => ['nullable', 'string', 'max:500'],
    ]);

    \App\Models\TeacherRoleRequest::create([
        'user_id' => Auth::id(),
        'status'  => 'PENDING',
        'comment' => $request->comment,
    ]);

    return back()->with('success', 'Заявка подана. Ожидайте решения деканата.');
}

}

