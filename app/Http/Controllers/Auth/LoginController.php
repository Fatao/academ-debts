<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email', 'regex:/@edu\.ugrasu\.ru$/'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Введите адрес электронной почты.',
            'email.email'       => 'Некорректный формат email.',
            'email.regex'       => 'Вход доступен только для адресов @edu.ugrasu.ru.',
            'password.required' => 'Введите пароль.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Неверный email или пароль.',
        ])->withInput($request->only('email'));
    }
}