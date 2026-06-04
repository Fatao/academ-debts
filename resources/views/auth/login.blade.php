<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — Система учёта задолженностей</title>
    <link rel="stylesheet" href="{{ asset('build/assets/' . basename(glob(public_path('build/assets/app-*.css'))[0])) }}">
    <style>
        html, body { height: 100%; margin: 0; }
        body {
            background: #f4f6f9;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .login-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
        }
        .university-logo {
            max-height: 72px;
            width: auto;
        }
        .site-footer {
            text-align: center;
            padding: 1rem;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            background: #fff;
        }
        .site-footer a { color: #6c757d; }
    </style>
</head>
<body>

<div class="login-wrapper">

    {{-- Логотип и название --}}
    <div class="text-center mb-4">


        @php
            $logoPath = null;
            foreach (['logo.svg', 'logo.png', 'logo.jpg'] as $f) {
                if (file_exists(public_path('images/' . $f))) {
                    $logoPath = asset('images/' . $f);
                    break;
                }
           }
        @endphp

        @if($logoPath)
            <img src="{{ $logoPath }}" alt="Логотип ЮГУ" class="university-logo mb-2">




        @else
            <div style="width:72px;height:72px;background:#0d6efd;border-radius:50%;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;">
                <span style="color:#fff;font-size:24px;font-weight:700;">ЮГУ</span>
            </div>
        @endif
        <div style="font-size:13px;color:#6c757d;max-width:320px;margin:0 auto;">
            Система учёта академических задолженностей
        </div>
    </div>

    {{-- Карточка входа --}}
    <div class="login-card">
        <div class="card border shadow-sm">
            <div class="card-header bg-white text-center py-3">
                <strong>Вход в систему</strong>
                <div class="text-muted mt-1" style="font-size:12px;">
                    Пожалуйста введите ваши данные для авторизации
                </div>
            </div>
            <div class="card-body px-4 py-4">

                @if(session('success'))
                    <div class="alert alert-success py-2" style="font-size:13px;">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger py-2" style="font-size:13px;">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Электронная почта</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email"
                               value="{{ old('email') }}"
                               autofocus required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <input type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password" required>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember" style="font-size:13px;">Запомнить меня</label>
                    </div>

                    <div class="d-grid mb-2">
                        <button type="submit" class="btn btn-primary">Войти</button>
                    </div>
                </form>

            </div>
            <div class="card-footer bg-white text-center py-3">
                <small>Нет аккаунта? <a href="{{ route('register') }}">Зарегистрироваться</a></small>
            </div>
        </div>
    </div>

</div>

{{-- Подвал --}}
<footer class="site-footer">
    &copy; ФГБОУ ВО Югорский государственный университет @ 2026.<br>
</footer>

<script src="{{ asset('build/assets/' . basename(glob(public_path('build/assets/app-*.js'))[0])) }}"></script>
</body>
</html>