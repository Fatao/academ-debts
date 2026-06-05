@extends('layouts.app')

@section('title', 'Мои заказы')
@section('page-title', 'Мои заказы')

@section('content')
@if($retakes->isEmpty())
    <div class="alert alert-info">Заказы не назначены.</div>
@else
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th>Заказ</th>
                <th>Дата и время</th>
                <th>Место</th>
                <th>Тип</th>
                <th>Заказчик</th>
                <th>Статус</th>
                <th>Результат</th>
                <th>Оценка</th>
            </tr>
        </thead>
        <tbody>
            @foreach($retakes as $retake)
            <tr>
                <td>{{ $retake->discipline->name }}</td>
                <td>{{ $retake->start_datetime->format('d.m.Y H:i') }}</td>
                <td>{{ $retake->location() }}</td>
                <td>{{ $retake->typeLabel() }}</td>
                <td>
                    @foreach($retake->jobgivers as $jobgiver)
                        <div>{{ $jobgiver->shortName() }}</div>
                    @endforeach
                </td>
                <td><span class="badge bg-secondary">{{ $retake->statusLabel() }}</span></td>
                <td>
                    @php $result = $retake->pivot->result_status; @endphp
                    @if($result === 'PASSED')
                        <span class="badge bg-success">Сдал</span>
                    @elseif($result === 'FAILED')
                        <span class="badge bg-danger">Не сдал</span>
                    @else
                        <span class="badge bg-secondary">Не проводилась</span>
                    @endif
                </td>
                <td>{{ $retake->pivot->grade_value ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
