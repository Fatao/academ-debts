@extends('layouts.app')

@section('title', 'Задолженности по моим дисциплинам')
@section('page-title', 'Задолженности по моим дисциплинам')

@section('page-actions')
    <a href="{{ route('teacher.debts.create') }}" class="btn btn-primary btn-sm">Выставить задолженность</a>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
@endif

@if($debts->isEmpty())
    <div class="alert alert-info">Задолженностей нет.</div>
@else
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Студент</th>
                <th>Группа</th>
                <th>Дисциплина</th>
                <th>Статус</th>
                <th>Оценка</th>
                <th>Дата</th>
                <th>Действие</th>
            </tr>
        </thead>
        <tbody>
            @foreach($debts as $i => $debt)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $debt->student->fullName() }}</td>
                <td>{{ $debt->student->group->name ?? '—' }}</td>
                <td>{{ $debt->discipline->name }}</td>
                <td>
                    @if($debt->isOpen())
                        <span class="badge bg-danger">Задолженность</span>
                    @else
                        <span class="badge bg-success">Закрыта</span>
                    @endif
                </td>
                <td>{{ $debt->gradeLabel() }}</td>
                <td>{{ $debt->updated_at->format('d.m.Y') }}</td>
                <td>
                    @if($debt->isOpen())
                        <button class="btn btn-sm btn-success"
                                data-bs-toggle="modal"
                                data-bs-target="#closeModal{{ $debt->id }}">
                            Закрыть
                        </button>

                        <div class="modal fade" id="closeModal{{ $debt->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h6 class="modal-title">Закрыть задолженность</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="{{ route('teacher.debts.close', $debt) }}">
                                        @csrf
                                        <div class="modal-body">
                                            <p class="mb-3">
                                                <strong>{{ $debt->student->fullName() }}</strong><br>
                                                <span class="text-muted">{{ $debt->discipline->name }}</span>
                                            </p>

                                            <div class="mb-3">
                                                <label class="form-label">Тип оценивания <span class="text-danger">*</span></label>
                                                <select name="grade_scale" class="form-select" required
                                                        onchange="toggleGradeInput({{ $debt->id }}, this.value)">
                                                    <option value="">— Выберите —</option>
                                                    <option value="EXAM">Экзамен (5, 4, 3, 2, Незачёт)</option>
                                                    <option value="PASS_FAIL">Зачёт / Незачёт</option>
                                                </select>
                                            </div>

                                            <div class="mb-3" id="exam-grade-{{ $debt->id }}" style="display:none;">
                                                <label class="form-label">Оценка <span class="text-danger">*</span></label>
                                                <select name="grade_value" class="form-select"
                                                        id="exam-select-{{ $debt->id }}">
                                                    <option value="">— Выберите оценку —</option>
                                                    <option value="5">5 — Отлично</option>
                                                    <option value="4">4 — Хорошо</option>
                                                    <option value="3">3 — Удовлетворительно</option>
                                                    <option value="2">2 — Неудовлетворительно</option>
                                                    <option value="0">Незачёт</option>
                                                </select>
                                            </div>

                                            <div class="mb-3" id="pass-grade-{{ $debt->id }}" style="display:none;">
                                                <label class="form-label">Результат <span class="text-danger">*</span></label>
                                                <select name="grade_value" class="form-select"
                                                        id="pass-select-{{ $debt->id }}">
                                                    <option value="">— Выберите —</option>
                                                    <option value="1">Зачёт</option>
                                                    <option value="0">Незачёт</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-sm"
                                                    data-bs-dismiss="modal">Отмена</button>
                                            <button type="submit" class="btn btn-success btn-sm">
                                                Сохранить оценку
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <span class="text-muted" style="font-size:12px;">Закрыта</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif

@endsection

@section('scripts')
<script>
function toggleGradeInput(debtId, scale) {
    const examDiv = document.getElementById('exam-grade-' + debtId);
    const passDiv = document.getElementById('pass-grade-' + debtId);
    const examSel = document.getElementById('exam-select-' + debtId);
    const passSel = document.getElementById('pass-select-' + debtId);

    examDiv.style.display = 'none';
    passDiv.style.display = 'none';
    examSel.removeAttribute('required');
    passSel.removeAttribute('required');
    examSel.value = '';
    passSel.value = '';

    if (scale === 'EXAM') {
        examDiv.style.display = 'block';
        examSel.setAttribute('required', 'required');
    } else if (scale === 'PASS_FAIL') {
        passDiv.style.display = 'block';
        passSel.setAttribute('required', 'required');
    }
}
</script>
@endsection