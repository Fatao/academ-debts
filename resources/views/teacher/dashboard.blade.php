@extends('layouts.app')

@section('title', 'Кабинет заказчика')
@section('page-title', 'Кабинет заказчика')

@section('content')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border">
            <div class="card-body">
                <div class="text-muted" style="font-size:12px;">ОТКРЫТЫХ ЗАКАЗОВ</div>
                <div class="fs-3 fw-bold text-danger">{{ $openDebts }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border">
            <div class="card-body">
                <div class="text-muted" style="font-size:12px;">ЗАКРЫТЫХ ЗАКАЗОВ</div>
                <div class="fs-3 fw-bold text-success">{{ $closedDebts }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border">
            <div class="card-body">
                <div class="text-muted" style="font-size:12px;">ПРЕДСТОЯЩИХ СДАЧ</div>
                <div class="fs-3 fw-bold text-primary">{{ $retakes }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border">
            <div class="card-body">
                <div class="text-muted" style="font-size:12px;">МОИ ЗАКАЗЫ</div>
                <div class="fs-3 fw-bold">{{ auth()->user()->disciplines->count() }}</div>
            </div>
        </div>
    </div>
</div>

<h6 class="border-bottom pb-2 mb-3">Последние задолженности по моим заказам</h6>
@if($recentDebts->isEmpty())
    <p class="text-muted">Задолженностей нет.</p>
@else
    <table class="table table-bordered table-hover table-sm">
        <thead>
            <tr>
                <th>Фрилансер</th>
                <th>Заказ</th>
                <th>Статус</th>
                <th>Дата</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentDebts as $debt)
            <tr>
                <td>{{ $debt->freelancer->fullName() }}</td>
                <td>{{ $debt->discipline->name }}</td>
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
    <a href="{{ route('jobgiver.debts') }}" class="btn btn-sm btn-outline-primary">Все заказы</a>
@endif
@endsection
