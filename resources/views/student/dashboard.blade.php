@extends('layouts.app')

@section('title', 'Личный кабинет фрилансера')
@section('page-title', 'Личный кабинет')

@section('content')
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border">
            <div class="card-body">
                <div class="text-muted" style="font-size:12px;">АКТИВНЫЕ ЗАКАЗЫ</div>
                <div class="fs-3 fw-bold text-danger">{{ $totalDebts }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border">
            <div class="card-body">
                <div class="text-muted" style="font-size:12px;">ЗАКРЫТЫХ ЗАКАЗОВ</div>
                <div class="fs-3 fw-bold text-success">{{ $closedDebts }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border">
            <div class="card-body">
                <div class="text-muted" style="font-size:12px;">ПРЕДСТОЯЩИХ ЗАКАЗОВ</div>
                <div class="fs-3 fw-bold text-primary">{{ $upcomingRetakes }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-7">
        <h6 class="border-bottom pb-2 mb-3">Мои заказы</h6>
        @if($debts->isEmpty())
            <p class="text-muted">Заказов нет.</p>
        @else
            <table class="table table-bordered table-hover table-sm">
                <thead>
                    <tr>
                        <th>Категория</th>
                        <th>Заказчик</th>
                        <th>Статус</th>
                        <th>Оценка</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($debts as $debt)
                    <tr>
                        <td>{{ $debt->discipline->name }}</td>
                        <td>{{ $debt->assignedBy->shortName() }}</td>
                        <td>
                            @if($debt->isOpen())
                                <span class="badge bg-danger">Заказ</span>
                            @else
                                <span class="badge bg-success">Закрыт</span>
                            @endif
                        </td>
                        <td>
                            @if($debt->grade_value !== null)
                                {{ $debt->grade_value }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="col-md-5">
        <h6 class="border-bottom pb-2 mb-3">Предстоящие заказы</h6>
        @if($retakes->isEmpty())
            <p class="text-muted">Пересдач не назначено.</p>
        @else
            @foreach($retakes as $retake)
            <div class="border rounded p-2 mb-2" style="font-size:13px;">
                <div class="fw-bold">{{ $retake->discipline->name }}</div>
                <div class="text-muted">{{ $retake->start_datetime->format('d.m.Y в H:i') }}</div>
                <div>{{ $retake->location() }}</div>
                <div class="mt-1">
                    <span class="badge bg-secondary">{{ $retake->statusLabel() }}</span>
                    <span class="badge bg-light text-dark border">{{ $retake->typeLabel() }}</span>
                </div>
            </div>
            @endforeach
        @endif
    </div>
</div>
@endsection
