@extends('layouts.app')

@section('title', 'Мои заказы')
@section('page-title', 'Заказы')

@section('content')
@if($retakes->isEmpty())
    <div class="alert alert-info">У вас нет ни одного заказа.</div>
@else
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th>Заказ</th>
                <th>Дата и время</th>
                <th>Место</th>
                <th>Тип</th>
                <th>Фрилансеры</th>
                <th>Статус</th>
            </tr>
        </thead>
        <tbody>
            @foreach($retakes as $retake)
            <tr>
                <td>{{ $retake->discipline->name }}</td>
                <td>{{ $retake->start_datetime->format('d.m.Y H:i') }}</td>
                <td>{{ $retake->location() }}</td>
                <td>{{ $retake->typeLabel() }}</td>
                <td>{{ $retake->freelancers->count() }}</td>
                <td><span class="badge bg-secondary">{{ $retake->statusLabel() }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
