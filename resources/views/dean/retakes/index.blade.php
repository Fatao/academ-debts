@extends('layouts.app')

@section('title', 'Пересдачи')
@section('page-title', 'Пересдачи')

@section('page-actions')
    <a href="{{ route('moderator.retakes.create') }}" class="btn btn-primary btn-sm">Назначить пересдачу</a>
@endsection

@section('content')
@if($retakes->isEmpty())
    <div class="alert alert-info">Пересдач не назначено.</div>
@else
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Дисциплина</th>
                <th>Дата и время</th>
                <th>Место</th>
                <th>Тип</th>
                <th>Преподаватели</th>
                <th>Студентов</th>
                <th>Статус</th>
            </tr>
        </thead>
        <tbody>
            @foreach($retakes as $i => $retake)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $retake->discipline->name }}</td>
                <td>{{ $retake->start_datetime->format('d.m.Y H:i') }}</td>
                <td>{{ $retake->location() }}</td>
                <td>{{ $retake->typeLabel() }}</td>
                <td>
                    @foreach($retake->jobgivers as $t)
                        <div>{{ $t->shortName() }}</div>
                    @endforeach
                </td>
                <td>{{ $retake->freelancers->count() }}</td>
                <td>
                    @if($retake->status === 'SCHEDULED')
                        <span class="badge bg-primary">Назначена</span>
                    @elseif($retake->status === 'IN_PROGRESS')
                        <span class="badge bg-warning text-dark">Проводится</span>
                    @else
                        <span class="badge bg-secondary">Завершена</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
