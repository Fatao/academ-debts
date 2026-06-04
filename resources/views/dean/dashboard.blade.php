@extends('layouts.app')

@section('title', 'Панель деканата')
@section('page-title', 'Панель деканата')

@section('content')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border">
            <div class="card-body">
                <div class="text-muted" style="font-size:12px;">АКТИВНЫХ ДОЛГОВ</div>
                <div class="fs-3 fw-bold text-danger">{{ $totalDebts }}</div>
                <a href="{{ route('dean.debts') }}" class="text-decoration-none" style="font-size:12px;">Просмотреть</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border">
            <div class="card-body">
                <div class="text-muted" style="font-size:12px;">ЗАКРЫТЫХ ДОЛГОВ</div>
                <div class="fs-3 fw-bold text-success">{{ $closedDebts }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border">
            <div class="card-body">
                <div class="text-muted" style="font-size:12px;">ВСЕГО ПЕРЕСДАЧ</div>
                <div class="fs-3 fw-bold text-primary">{{ $totalRetakes }}</div>
                <a href="{{ route('dean.retakes.index') }}" class="text-decoration-none" style="font-size:12px;">Просмотреть</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border">
            <div class="card-body">
                <div class="text-muted" style="font-size:12px;">ЗАЯВОК НА РАССМОТРЕНИИ</div>
                <div class="fs-3 fw-bold {{ $pendingRequests > 0 ? 'text-warning' : '' }}">{{ $pendingRequests }}</div>
                <a href="{{ route('dean.requests') }}" class="text-decoration-none" style="font-size:12px;">Рассмотреть</a>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
    <h6 class="mb-0">Последние задолженности</h6>
    <a href="{{ route('dean.retakes.create') }}" class="btn btn-primary btn-sm">Назначить пересдачу</a>
</div>

<table class="table table-bordered table-hover table-sm">
    <thead>
        <tr>
            <th>Студент</th>
            <th>Группа</th>
            <th>Дисциплина</th>
            <th>Преподаватель</th>
            <th>Статус</th>
            <th>Дата</th>
        </tr>
    </thead>
    <tbody>
        @foreach($recentDebts as $debt)
        <tr>
            <td>{{ $debt->student->fullName() }}</td>
            <td>{{ $debt->student->group->name ?? '—' }}</td>
            <td>{{ $debt->discipline->name }}</td>
            <td>{{ $debt->assignedBy->shortName() }}</td>
            <td>
                @if($debt->isOpen())
                    <span class="badge bg-danger">Задолженность</span>
                @else
                    <span class="badge bg-success">Закрыта</span>
                @endif
            </td>
            <td>{{ $debt->created_at->format('d.m.Y') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection