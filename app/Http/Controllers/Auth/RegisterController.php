<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        $groupsByYear = Group::orderBy('name')->get()->groupBy('year');
        return view('auth.register', compact('groupsByYear'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'last_name'             => ['required', 'string', 'max:100'],
            'first_name'            => ['required', 'string', 'max:100'],
            'middle_name'           => ['nullable', 'string', 'max:100'],
            'email'                 => ['required', 'email', 'unique:users,email', 'regex:/@edu\.ugrasu\.ru$/'],
            'role'                  => ['required', 'in:freelancer,jobgiver'],
            'group_id'              => ['nullable', 'exists:groups,id'],
            'password'              => ['required', 'min:6', 'confirmed'],
        ], [
            'last_name.required'  => 'Введите фамилию.',
            'first_name.required' => 'Введите имя.',
            'email.required'      => 'Введите email.',
            'email.email'         => 'Некорректный формат email.',
            'email.unique'        => 'Этот email уже зарегистрирован.',
            'email.regex'         => 'Регистрация доступна только для адресов @edu.ugrasu.ru.',
            'role.required'       => 'Выберите роль.',
            'password.required'   => 'Введите пароль.',
            'password.min'        => 'Пароль должен содержать не менее 6 символов.',
            'password.confirmed'  => 'Пароли не совпадают.',
        ]);

        User::create([
            'last_name'   => $request->last_name,
            'first_name'  => $request->first_name,
            'middle_name' => $request->middle_name,
            'email'       => $request->email,
            'group_id'    => $request->role === 'freelancer' ? $request->group_id : null,
            'is_jobgiver'  => $request->role === 'jobgiver',
            'password'    => Hash::make($request->password),
        ]);

        return redirect()->route('login')
            ->with('success', 'Регистрация прошла успешно. Войдите в систему.');
    }
}
