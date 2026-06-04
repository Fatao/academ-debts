@extends('layouts.app')

@section('title', 'Мои задолженности')
@section('page-title', 'Мои задолженности')

@section('content')
@if($debts->isEmpty())
    <div class="alert alert-info">У вас нет задолженностей.</div>
@else
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Дисциплина</th>
                <th>Преподаватель</th>
                <th>Статус</th>
                <th>Оценка</th>
                <th>Тип оценивания</th>
                <th>Дата изменения</th>
                <th>Комментарий</th>
            </tr>
        </thead>
        <tbody>
            @foreach($debts as $i => $debt)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $debt->discipline->name }}</td>
                <td>{{ $debt->assignedBy->shortName() }}</td>
                <td>
                    @if($debt->isOpen())
                        <span class="badge bg-danger">Задолженность</span>
                    @else
                        <span class="badge bg-success">Закрыта</span>
                    @endif
                </td>
                <td>{{ $debt->gradeLabel() }}</td>
                <td>
                    @if($debt->grade_scale)
                        {{ \App\Models\Debt::GRADE_SCALES[$debt->grade_scale] ?? '—' }}
                    @else
                        —
                    @endif
                </td>
                <td>{{ $debt->updated_at->format('d.m.Y H:i') }}</td>
                <td>{{ $debt->comment ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection