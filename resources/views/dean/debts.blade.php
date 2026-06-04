@extends('layouts.app')

@section('title', 'Сводная таблица задолженностей')
@section('page-title', 'Сводная таблица задолженностей')

@section('content')
<table class="table table-bordered table-striped table-hover">
    <thead>
        <tr>
            <th>#</th>
            <th>Студент</th>
            <th>Группа</th>
            <th>Дисциплина</th>
            <th>Преподаватель</th>
            <th>Статус</th>
            <th>Оценка</th>
            <th>Тип оценивания</th>
            <th>Дата изменения</th>
        </tr>
    </thead>
    <tbody>
        @foreach($debts as $i => $debt)
        <tr>
            <td>{{ $i + 1 }}</td>
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
            <td>{{ $debt->gradeLabel() }}</td>
            <td>
                @if($debt->grade_scale)
                    {{ \App\Models\Debt::GRADE_SCALES[$debt->grade_scale] ?? '—' }}
                @else
                    —
                @endif
            </td>
            <td>{{ $debt->updated_at->format('d.m.Y H:i') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection