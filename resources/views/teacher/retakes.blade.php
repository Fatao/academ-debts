@extends('layouts.app')

@section('title', 'Мои пересдачи')
@section('page-title', 'Пересдачи')

@section('content')
@if($retakes->isEmpty())
    <div class="alert alert-info">Вам не назначено ни одной пересдачи.</div>
@else
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th>Дисциплина</th>
                <th>Дата и время</th>
                <th>Место</th>
                <th>Тип</th>
                <th>Студентов</th>
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
                <td>{{ $retake->students->count() }}</td>
                <td><span class="badge bg-secondary">{{ $retake->statusLabel() }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection