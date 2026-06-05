<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация — Маркетплейс для фриланса</title>
    <link rel="stylesheet" href="{{ asset('build/assets/' . basename(glob(public_path('build/assets/app-*.css'))[0])) }}">
    <style>
        html, body { height: 100%; margin: 0; }
        body {
            background: #f4f6f9;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .register-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 2rem 1rem;
        }
        .register-card { width: 100%; max-width: 520px; }
        .university-logo { max-height: 72px; width: auto; }
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

<div class="register-wrapper">

    {{-- Логотип --}}
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
            <img src="{{ $logoPath }}" alt="Логотип ЮГУ" class="university-logo mb-3">
        @else
            <div style="width:64px;height:64px;background:#0d6efd;border-radius:50%;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
                <span style="color:#fff;font-size:20px;font-weight:700;">ЮГУ</span>
            </div>
        @endif
        <div style="font-size:13px;color:#6c757d;">
            Маркетплейс для фриланса
        </div>
    </div>

    <div class="register-card">
        <div class="card border shadow-sm">
            <div class="card-header bg-white text-center py-3">
                <strong>Регистрация</strong>
            </div>
            <div class="card-body px-4 py-4">

                @if ($errors->any())
                    <div class="alert alert-danger py-2" style="font-size:13px;">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" id="reg-form">
                    @csrf

                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Фамилия <span class="text-danger">*</span></label>
                            <input type="text" name="last_name"
                                   class="form-control @error('last_name') is-invalid @enderror"
                                   value="{{ old('last_name') }}" required>
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Имя <span class="text-danger">*</span></label>
                            <input type="text" name="first_name"
                                   class="form-control @error('first_name') is-invalid @enderror"
                                   value="{{ old('first_name') }}" required>
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col">
                            <label class="form-label">Отчество</label>
                            <input type="text" name="middle_name" class="form-control"
                                   value="{{ old('middle_name') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Электронная почта <span class="text-danger">*</span></label>
                        <input type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Роль --}}
                    <div class="mb-3">
                        <label class="form-label">Регистрируюсь как <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="role"
                                       id="role_freelancer" value="freelancer"
                                       {{ old('role', 'freelancer') === 'freelancer' ? 'checked' : '' }}>
                                <label class="form-check-label" for="role_freelancer">Студент</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="role"
                                       id="role_jobgiver" value="jobgiver"
                                       {{ old('role') === 'jobgiver' ? 'checked' : '' }}>
                                <label class="form-check-label" for="role_jobgiver">Преподаватель</label>
                            </div>
                        </div>
                    </div>

                    {{-- Секция фрилансера --}}
                    <div id="freelancer-section">
                        <div class="mb-3">
                            <label class="form-label">Курс обучения</label>
                            <select id="year-select" class="form-select">
                                <option value="">— Выберите курс —</option>
                                @for($y = 1; $y <= 6; $y++)
                                    <option value="{{ $y }}" {{ old('year') == $y ? 'selected' : '' }}>
                                        {{ $y }} курс
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="mb-3" id="group-section" style="display:none;">
                            <label class="form-label">Группа</label>
                            <select name="group_id" id="group-select"
                                    class="form-select @error('group_id') is-invalid @enderror">
                                <option value="">— Выберите группу —</option>
                            </select>
                            @error('group_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Пароль <span class="text-danger">*</span></label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Подтверждение пароля <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
                    </div>
                </form>

            </div>
            <div class="card-footer bg-white text-center py-3">
                <small>Уже есть аккаунт? <a href="{{ route('login') }}">Войти</a></small>
            </div>
        </div>

        <p class="text-center text-muted mt-3" style="font-size:12px;">
            После регистрации фрилансеры получают доступ сразу.<br>
            Работодатели — после подтверждения администратором.
        </p>
    </div>

</div>

<footer class="site-footer">
    &copy; ФГБОУ ВО Югорский государственный университет @ 2026<br>
    
</footer>

<script>
const groupsByYear = @json($groupsByYear);
</script>
<script src="{{ asset('build/assets/' . basename(glob(public_path('build/assets/app-*.js'))[0])) }}"></script>
<script>
const yearSelect     = document.getElementById('year-select');
const groupSection   = document.getElementById('group-section');
const groupSelect    = document.getElementById('group-select');
const freelancerSection = document.getElementById('freelancer-section');
const roleInputs     = document.querySelectorAll('input[name="role"]');

function toggleFreelancerSection() {
    const isFreelancer = document.querySelector('input[name="role"]:checked')?.value === 'freelancer';
    freelancerSection.style.display = isFreelancer ? 'block' : 'none';
}

function updateGroups() {
    const year = yearSelect.value;
    groupSelect.innerHTML = '<option value="">— Выберите группу —</option>';
    if (year && groupsByYear[year]) {
        groupsByYear[year].forEach(function(g) {
            const opt = document.createElement('option');
            opt.value = g.id;
            opt.textContent = g.name;
            groupSelect.appendChild(opt);
        });
        groupSection.style.display = 'block';
    } else {
        groupSection.style.display = 'none';
    }
}

roleInputs.forEach(r => r.addEventListener('change', toggleFreelancerSection));
yearSelect.addEventListener('change', updateGroups);
toggleFreelancerSection();

@if(old('year'))
    yearSelect.value = '{{ old('year') }}';
    updateGroups();
    @if(old('group_id'))
        groupSelect.value = '{{ old('group_id') }}';
    @endif
@endif
</script>
</body>
</html>
