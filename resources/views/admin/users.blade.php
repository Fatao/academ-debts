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
                @if($user->is_moderator)
                    <span class="badge bg-primary">Модератор</span>
                @endif
                @if($user->is_jobgiver)
                    <span class="badge bg-info text-dark">Заказчик</span>
                @endif
                @if($user->isFreelancer())
                    <span class="badge bg-secondary">Фрилансер</span>
                @endif
            </td>
            <td>
                @if(!$user->isAdmin())
                    <form method="POST" action="{{ route('admin.users.toggle-moderator', $user) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $user->is_moderator ? 'btn-outline-danger' : 'btn-outline-primary' }}">
                            {{ $user->is_moderator ? 'Снять деканат' : 'Назначить модератора' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.users.toggle-jobgiver', $user) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $user->is_jobgiver ? 'btn-outline-danger' : 'btn-outline-info' }}">
                            {{ $user->is_jobgiver ? 'Снять преподавателя' : 'Назначить заказчиком' }}
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
