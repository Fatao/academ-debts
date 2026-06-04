@extends('layouts.app')

@section('title', 'Результаты пересдачи')
@section('page-title', 'Результаты пересдачи')

@section('content')
<div class="mb-3 p-3 border rounded bg-light" style="font-size:13px;">
    <strong>Дисциплина:</strong> {{ $retake->discipline->name }} &nbsp;|&nbsp;
    <strong>Дата:</strong> {{ $retake->start_datetime->format('d.m.Y H:i') }} &nbsp;|&nbsp;
    <strong>Место:</strong> {{ $retake->location() }} &nbsp;|&nbsp;
    <strong>Тип:</strong> {{ $retake->typeLabel() }}
</div>

@if(session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('teacher.retakes.results.save', $retake) }}">
    @csrf
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Студент</th>
                <th>Группа</th>
                <th>Результат</th>
                <th>Тип оценивания</th>
                <th>Оценка</th>
            </tr>
        </thead>
        <tbody>
            @foreach($retake->students as $student)
            <tr>
                <td>{{ $student->fullName() }}</td>
                <td>{{ $student->group->name ?? '—' }}</td>
                <td>
                    <select name="results[{{ $student->id }}][result_status]"
                            class="form-select form-select-sm" required>
                        <option value="NOT_TAKEN" {{ $student->pivot->result_status === 'NOT_TAKEN' ? 'selected' : '' }}>
                            Не явился
                        </option>
                        <option value="PASSED" {{ $student->pivot->result_status === 'PASSED' ? 'selected' : '' }}>
                            Сдал
                        </option>
                        <option value="FAILED" {{ $student->pivot->result_status === 'FAILED' ? 'selected' : '' }}>
                            Не сдал
                        </option>
                    </select>
                </td>
                <td>
                    <select name="results[{{ $student->id }}][grade_scale]"
                            class="form-select form-select-sm"
                            id="scale-{{ $student->id }}"
                            onchange="toggleResultGrade({{ $student->id }}, this.value)">
                        <option value="">— Выберите —</option>
                        <option value="EXAM"
                            {{ $student->pivot->grade_scale === 'EXAM' ? 'selected' : '' }}>
                            Экзамен (5, 4, 3, 2, Незачёт)
                        </option>
                        <option value="PASS_FAIL"
                            {{ $student->pivot->grade_scale === 'PASS_FAIL' ? 'selected' : '' }}>
                            Зачёт / Незачёт
                        </option>
                    </select>
                </td>
                <td>
                    <div id="exam-div-{{ $student->id }}"
                         style="display:{{ $student->pivot->grade_scale === 'EXAM' ? 'block' : 'none' }}">
                        <select name="results[{{ $student->id }}][grade_value]"
                                class="form-select form-select-sm"
                                id="exam-sel-{{ $student->id }}">
                            <option value="">—</option>
                            <option value="5" {{ $student->pivot->grade_value == 5 ? 'selected' : '' }}>5 — Отлично</option>
                            <option value="4" {{ $student->pivot->grade_value == 4 ? 'selected' : '' }}>4 — Хорошо</option>
                            <option value="3" {{ $student->pivot->grade_value == 3 ? 'selected' : '' }}>3 — Удовлетворительно</option>
                            <option value="2" {{ $student->pivot->grade_value == 2 ? 'selected' : '' }}>2 — Неудовлетворительно</option>
                            <option value="0" {{ $student->pivot->grade_value == 0 && $student->pivot->grade_scale === 'EXAM' ? 'selected' : '' }}>Незачёт</option>
                        </select>
                    </div>
                    <div id="pass-div-{{ $student->id }}"
                         style="display:{{ $student->pivot->grade_scale === 'PASS_FAIL' ? 'block' : 'none' }}">
                        <select name="results[{{ $student->id }}][grade_value]"
                                class="form-select form-select-sm"
                                id="pass-sel-{{ $student->id }}">
                            <option value="">—</option>
                            <option value="1" {{ $student->pivot->grade_value == 1 ? 'selected' : '' }}>Зачёт</option>
                            <option value="0" {{ $student->pivot->grade_value == 0 && $student->pivot->grade_scale === 'PASS_FAIL' ? 'selected' : '' }}>Незачёт</option>
                        </select>
                    </div>
                    <div id="none-div-{{ $student->id }}"
                         style="display:{{ !$student->pivot->grade_scale ? 'block' : 'none' }}">
                        <span class="text-muted" style="font-size:12px;">Выберите тип</span>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Сохранить результаты</button>
        <a href="{{ route('teacher.retakes') }}" class="btn btn-secondary">Назад</a>
    </div>
</form>
@endsection

@section('scripts')
<script>
function toggleResultGrade(studentId, scale) {
    const examDiv = document.getElementById('exam-div-' + studentId);
    const passDiv = document.getElementById('pass-div-' + studentId);
    const noneDiv = document.getElementById('none-div-' + studentId);

    examDiv.style.display = 'none';
    passDiv.style.display = 'none';
    noneDiv.style.display = 'none';

    if (scale === 'EXAM')      examDiv.style.display = 'block';
    else if (scale === 'PASS_FAIL') passDiv.style.display = 'block';
    else                       noneDiv.style.display = 'block';
}
</script>
@endsection