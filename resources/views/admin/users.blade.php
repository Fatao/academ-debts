@extends('layouts.app')

@section('title', 'Управление пользователями')
@section('page-title', 'Пользователи системы')

@section('content')
<table class="table table-bordered table-striped table-hover">
    <thead>
        <tr>
            <th>#</th>
            <th>ФИО</th>
            <th>Email</th>
            <th>Группа</th>
            <th>Роль</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $i => $user)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $user->fullName() }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->group->name ?? '—' }}</td>
            <td>
                @if($user->is_admin)
                    <span class="badge bg-dark">Администратор</span>
                @endif
                @if($user->is_dean)
                    <span class="badge bg-primary">Деканат</span>
                @endif
                @if($user->is_teacher)
                    <span class="badge bg-info text-dark">Преподаватель</span>
                @endif
                @if($user->isStudent())
                    <span class="badge bg-secondary">Студент</span>
                @endif
            </td>
            <td>
                @if(!$user->isAdmin())
                    <form method="POST" action="{{ route('admin.users.toggle-dean', $user) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $user->is_dean ? 'btn-outline-danger' : 'btn-outline-primary' }}">
                            {{ $user->is_dean ? 'Снять деканат' : 'Назначить деканат' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.users.toggle-teacher', $user) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $user->is_teacher ? 'btn-outline-danger' : 'btn-outline-info' }}">
                            {{ $user->is_teacher ? 'Снять преподавателя' : 'Назначить преподавателем' }}
                        </button>
                    </form>
                @else
                    <span class="text-muted" style="font-size:12px;">Системный аккаунт</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection