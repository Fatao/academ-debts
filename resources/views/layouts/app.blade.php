<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Система учёта задолженностей')</title>
    <link rel="stylesheet" href="{{ asset('build/assets/' . basename(glob(public_path('build/assets/app-*.css'))[0])) }}">
    <style>
        body { font-size: 14px; }
        .navbar-brand { font-weight: 600; letter-spacing: 0.3px; }
        .sidebar { min-height: calc(100vh - 56px); background: #f8f9fa; border-right: 1px solid #dee2e6; padding-top: 1rem; }
        .sidebar .nav-link { color: #343a40; padding: 0.45rem 1rem; border-radius: 0; }
        .sidebar .nav-link:hover { background: #e9ecef; color: #0d6efd; }
        .sidebar .nav-link.active { background: #0d6efd; color: #fff; }
        .sidebar .nav-section { font-size: 11px; text-transform: uppercase; color: #6c757d; padding: 0.75rem 1rem 0.25rem; letter-spacing: 0.5px; }
        .main-content { padding: 1.5rem; }
        .badge-debt { background-color: #dc3545; }
        .badge-closed { background-color: #198754; }
        .table th { background-color: #f8f9fa; font-weight: 600; font-size: 13px; }
        .notification-dot { width: 8px; height: 8px; background: #dc3545; border-radius: 50%; display: inline-block; margin-left: 4px; vertical-align: middle; }
    </style>
</head>
<body>

{{-- Навигационная панель --}}
<nav class="navbar navbar-dark bg-dark navbar-expand-lg px-3" style="height:56px;">
    <a class="navbar-brand" href="{{ route('dashboard') }}">
        Учёт задолженностей
    </a>
    <div class="ms-auto d-flex align-items-center gap-3">
        {{-- Уведомления --}}
        @auth
            @php $unread = auth()->user()->unreadNotifications()->count(); @endphp
            <a href="{{ route('notifications.index') }}" class="text-white text-decoration-none" style="font-size:13px;">
                Уведомления
                @if($unread > 0)
                    <span class="badge bg-danger ms-1">{{ $unread }}</span>
                @endif
            </a>
        @endauth

        {{-- Пользователь --}}
        <div class="dropdown">
            <a class="text-white text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" style="font-size:13px;">
                {{ auth()->user()->shortName() }}
                <span class="badge bg-secondary ms-1">{{ auth()->user()->roleLabel() }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><span class="dropdown-item-text text-muted" style="font-size:12px;">{{ auth()->user()->email }}</span></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">Выйти</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">

        {{-- Боковая панель --}}
        <div class="col-md-2 p-0 sidebar">
            <nav class="nav flex-column">
                @auth
                    @if(auth()->user()->isAdmin() || auth()->user()->isDean())
                        <span class="nav-section">Деканат</span>
                        <a href="{{ route('dean.dashboard') }}"
                           class="nav-link {{ request()->routeIs('dean.dashboard') ? 'active' : '' }}">
                            Главная
                        </a>
                        <a href="{{ route('dean.debts') }}"
                           class="nav-link {{ request()->routeIs('dean.debts') ? 'active' : '' }}">
                            Задолженности
                        </a>
                        <a href="{{ route('dean.retakes.index') }}"
                           class="nav-link {{ request()->routeIs('dean.retakes.*') ? 'active' : '' }}">
                            Пересдачи
                        </a>
                        <a href="{{ route('dean.requests') }}"
                           class="nav-link {{ request()->routeIs('dean.requests') ? 'active' : '' }}">
                            Заявки
                        </a>
                        <a href="{{ route('dean.reports') }}"
                           class="nav-link {{ request()->routeIs('dean.reports') ? 'active' : '' }}">
                            Отчёты
                        </a>
                        <a href="{{ route('dean.import') }}"
                           class="nav-link {{ request()->routeIs('dean.import*') ? 'active' : '' }}">
                            Импорт данных
                        </a>
                        @if(auth()->user()->isAdmin())
                            <span class="nav-section">Администрирование</span>
                            <a href="{{ route('admin.users') }}"
                               class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                                Пользователи
                            </a>
                            <a href="{{ route('admin.disciplines') }}"
                               class="nav-link {{ request()->routeIs('admin.disciplines') ? 'active' : '' }}">
                                Дисциплины
                            </a>
                            <a href="{{ route('admin.role-requests') }}"
                               class="nav-link {{ request()->routeIs('admin.role-requests') ? 'active' : '' }}">
                                Заявки на роль
                            </a>
                        @endif

                    @elseif(auth()->user()->isTeacher())
                        <span class="nav-section">Преподаватель</span>
                        <a href="{{ route('teacher.dashboard') }}"
                           class="nav-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
                            Главная
                        </a>
                        <a href="{{ route('teacher.debts') }}"
                           class="nav-link {{ request()->routeIs('teacher.debts*') ? 'active' : '' }}">
                            Задолженности
                        </a>
                        <a href="{{ route('teacher.debts.create') }}"
                           class="nav-link {{ request()->routeIs('teacher.debts.create') ? 'active' : '' }}">
                            Выставить задолженность
                        </a>
                        <a href="{{ route('teacher.retakes') }}"
                           class="nav-link {{ request()->routeIs('teacher.retakes*') ? 'active' : '' }}">
                            Пересдачи
                        </a>
                        <a href="{{ route('teacher.requests') }}"
                           class="nav-link {{ request()->routeIs('teacher.requests') ? 'active' : '' }}">
                            Мои заявки
                        </a>

                    @else
                        <span class="nav-section">Студент</span>
                        <a href="{{ route('student.dashboard') }}"
                           class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                            Главная
                        </a>
                        <a href="{{ route('student.debts') }}"
                           class="nav-link {{ request()->routeIs('student.debts') ? 'active' : '' }}">
                            Мои задолженности
                        </a>
                        <a href="{{ route('student.retakes') }}"
                           class="nav-link {{ request()->routeIs('student.retakes') ? 'active' : '' }}">
                            Мои пересдачи
                        </a>
                        <a href="{{ route('student.request-role') }}"
                           class="nav-link {{ request()->routeIs('student.request-role') ? 'active' : '' }}">
                            Заявка на преподавателя
                        </a>
                    @endif

                    <span class="nav-section">Общее</span>
                    <a href="{{ route('notifications.index') }}"
                       class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                        Уведомления
                        @if(isset($unread) && $unread > 0)
                            <span class="badge bg-danger">{{ $unread }}</span>
                        @endif
                    </a>
                @endauth
            </nav>
        </div>

        {{-- Основной контент --}}
        <div class="col-md-10 main-content">

            {{-- Заголовок страницы --}}
            @hasSection('page-title')
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="mb-0">@yield('page-title')</h5>
                    @hasSection('page-actions')
                        <div>@yield('page-actions')</div>
                    @endif
                </div>
            @endif

            {{-- Сообщения --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible py-2 fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible py-2 fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>

    </div>
</div>

<script src="{{ asset('build/assets/' . basename(glob(public_path('build/assets/app-*.js'))[0])) }}"></script>
@yield('scripts')
</body>
</html>