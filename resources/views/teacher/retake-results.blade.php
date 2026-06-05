@extends('layouts.app')

@section('title', 'Результаты сдачи заказа')
@section('page-title', 'Результаты сдачи заказа')

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

<form method="POST" action="{{ route('jobgiver.retakes.results.save', $retake) }}">
    @csrf
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Фрилансер</th>
                <th>Группа</th>
                <th>Результат</th>
                <th>Тип оценивания</th>
                <th>Оценка</th>
            </tr>
        </thead>
        <tbody>
            @foreach($retake->freelancers as $freelancer)
            <tr>
                <td>{{ $freelancer->fullName() }}</td>
                <td>{{ $freelancer->group->name ?? '—' }}</td>
                <td>
                    <select name="results[{{ $freelancer->id }}][result_status]"
                            class="form-select form-select-sm" required>
                        <option value="NOT_TAKEN" {{ $freelancer->pivot->result_status === 'NOT_TAKEN' ? 'selected' : '' }}>
                            Не явился
                        </option>
                        <option value="PASSED" {{ $freelancer->pivot->result_status === 'PASSED' ? 'selected' : '' }}>
                            Сдал
                        </option>
                        <option value="FAILED" {{ $freelancer->pivot->result_status === 'FAILED' ? 'selected' : '' }}>
                            Не сдал
                        </option>
                    </select>
                </td>
                <td>
                    <select name="results[{{ $freelancer->id }}][grade_scale]"
                            class="form-select form-select-sm"
                            id="scale-{{ $freelancer->id }}"
                            onchange="toggleResultGrade({{ $freelancer->id }}, this.value)">
                        <option value="">— Выберите —</option>
                        <option value="EXAM"
                            {{ $freelancer->pivot->grade_scale === 'EXAM' ? 'selected' : '' }}>
                            Экзамен (5, 4, 3, 2, Незачёт)
                        </option>
                        <option value="PASS_FAIL"
                            {{ $freelancer->pivot->grade_scale === 'PASS_FAIL' ? 'selected' : '' }}>
                            Зачёт / Незачёт
                        </option>
                    </select>
                </td>
                <td>
                    <div id="exam-div-{{ $freelancer->id }}"
                         style="display:{{ $freelancer->pivot->grade_scale === 'EXAM' ? 'block' : 'none' }}">
                        <select name="results[{{ $freelancer->id }}][grade_value]"
                                class="form-select form-select-sm"
                                id="exam-sel-{{ $freelancer->id }}">
                            <option value="">—</option>
                            <option value="5" {{ $freelancer->pivot->grade_value == 5 ? 'selected' : '' }}>5 — Отлично</option>
                            <option value="4" {{ $freelancer->pivot->grade_value == 4 ? 'selected' : '' }}>4 — Хорошо</option>
                            <option value="3" {{ $freelancer->pivot->grade_value == 3 ? 'selected' : '' }}>3 — Удовлетворительно</option>
                            <option value="2" {{ $freelancer->pivot->grade_value == 2 ? 'selected' : '' }}>2 — Неудовлетворительно</option>
                            <option value="0" {{ $freelancer->pivot->grade_value == 0 && $freelancer->pivot->grade_scale === 'EXAM' ? 'selected' : '' }}>Незачёт</option>
                        </select>
                    </div>
                    <div id="pass-div-{{ $freelancer->id }}"
                         style="display:{{ $freelancer->pivot->grade_scale === 'PASS_FAIL' ? 'block' : 'none' }}">
                        <select name="results[{{ $freelancer->id }}][grade_value]"
                                class="form-select form-select-sm"
                                id="pass-sel-{{ $freelancer->id }}">
                            <option value="">—</option>
                            <option value="1" {{ $freelancer->pivot->grade_value == 1 ? 'selected' : '' }}>Зачёт</option>
                            <option value="0" {{ $freelancer->pivot->grade_value == 0 && $freelancer->pivot->grade_scale === 'PASS_FAIL' ? 'selected' : '' }}>Незачёт</option>
                        </select>
                    </div>
                    <div id="none-div-{{ $freelancer->id }}"
                         style="display:{{ !$freelancer->pivot->grade_scale ? 'block' : 'none' }}">
                        <span class="text-muted" style="font-size:12px;">Выберите тип</span>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Сохранить результаты</button>
        <a href="{{ route('jobgiver.retakes') }}" class="btn btn-secondary">Назад</a>
    </div>
</form>
@endsection

@section('scripts')
<script>
function toggleResultGrade(freelancerId, scale) {
    const examDiv = document.getElementById('exam-div-' + freelancerId);
    const passDiv = document.getElementById('pass-div-' + freelancerId);
    const noneDiv = document.getElementById('none-div-' + freelancerId);

    examDiv.style.display = 'none';
    passDiv.style.display = 'none';
    noneDiv.style.display = 'none';

    if (scale === 'EXAM')      examDiv.style.display = 'block';
    else if (scale === 'PASS_FAIL') passDiv.style.display = 'block';
    else                       noneDiv.style.display = 'block';
}
</script>
@endsection
