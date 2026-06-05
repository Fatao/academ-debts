@extends('layouts.app')

@section('title', 'Назначить заказ')
@section('page-title', 'Назначить заказ')

@section('content')
<div class="row">
    <div class="col-md-9">

        @if($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('moderator.retakes.store') }}">
            @csrf

            {{-- Заказ + Тип --}}
            <div class="row mb-3">
                <div class="col-md-8">
                    <label class="form-label">Заказ <span class="text-danger">*</span></label>
                    <select name="discipline_id" class="form-select" required>
                        <option value="">— Выберите заказ —</option>
                        @foreach($disciplines as $d)
                            <option value="{{ $d->id }}" {{ old('discipline_id') == $d->id ? 'selected' : '' }}>
                                {{ $d->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Тип заказа <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required id="retake-type">
                        <option value="REGULAR"    {{ old('type','REGULAR') === 'REGULAR'    ? 'selected' : '' }}>Обычная</option>
                        <option value="COMMISSION" {{ old('type') === 'COMMISSION' ? 'selected' : '' }}>С комиссией (мин. 3 преп.)</option>
                    </select>
                </div>
            </div>

            {{-- Дата + Продолжительность --}}
            <div class="row mb-3">
                <div class="col-md-5">
                    <label class="form-label">Дата и время <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="start_datetime" class="form-control"
                           value="{{ old('start_datetime') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Продолжительность (мин.) <span class="text-danger">*</span></label>
                    <input type="number" name="duration_minutes" class="form-control"
                           min="15" value="{{ old('duration_minutes', 90) }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Корпус <span class="text-danger">*</span></label>
                    <input type="text" name="building_number" class="form-control"
                           maxlength="20" value="{{ old('building_number') }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Аудитория <span class="text-danger">*</span></label>
                    <input type="text" name="room_number" class="form-control"
                           maxlength="20" value="{{ old('room_number') }}" required>
                </div>
            </div>

            {{-- заказчики --}}
            <div class="mb-3">
                <label class="form-label">
                    Заказчики <span class="text-danger">*</span>
                    <span id="commission-hint" class="text-danger ms-2" style="font-size:12px;display:none;">
                        При комиссии необходимо выбрать минимум 3 преподавателей
                    </span>
                </label>
                <select name="jobgiver_ids[]" class="form-select" multiple size="6" required id="jobgiver-select">
                    @foreach($jobgivers as $t)
                        <option value="{{ $t->id }}"
                            {{ in_array($t->id, old('jobgiver_ids', [])) ? 'selected' : '' }}>
                            {{ $t->fullName() }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Удерживайте <kbd>Ctrl</kbd> для выбора нескольких преподавателей.</div>
            </div>

            <hr class="my-3">

            {{-- Фрилансеры: курс → группа → список --}}
            <label class="form-label">Студенты <span class="text-danger">*</span></label>

            <div class="row mb-2">
                <div class="col-md-3">
                    <select id="year-filter" class="form-select form-select-sm">
                        <option value="">— Курс —</option>
                        @for($y = 1; $y <= 6; $y++)
                            <option value="{{ $y }}">{{ $y }} курс</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="group-filter" class="form-select form-select-sm" disabled>
                        <option value="">— Группа —</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="select-all-btn" disabled>
                        Выбрать всех в группе
                    </button>
                </div>
            </div>

            <select name="freelancer_ids[]" class="form-select" multiple size="10" required id="freelancer-select">
                @foreach($freelancers as $s)
                    <option value="{{ $s->id }}"
                            data-year="{{ $s->group->year ?? '' }}"
                            data-group="{{ $s->group_id ?? '' }}"
                        {{ in_array($s->id, old('freelancer_ids', [])) ? 'selected' : '' }}>
                        {{ $s->fullName() }} {{ $s->group ? '(' . $s->group->name . ')' : '' }}
                    </option>
                @endforeach
            </select>
            <div class="form-text">Удерживайте <kbd>Ctrl</kbd> для выбора нескольких фрилансеров</div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Назначить пересдачу</button>
                <a href="{{ route('moderator.retakes.index') }}" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Данные групп по курсам
const groupsByYear = @json($groupsByYear);

const yearFilter    = document.getElementById('year-filter');
const groupFilter   = document.getElementById('group-filter');
const freelancerSelect = document.getElementById('freelancer-select');
const selectAllBtn  = document.getElementById('select-all-btn');
const retakeType    = document.getElementById('retake-type');
const commHint      = document.getElementById('commission-hint');

// Комиссия — подсказка
retakeType.addEventListener('change', function() {
    commHint.style.display = this.value === 'COMMISSION' ? 'inline' : 'none';
});

// Год → Группы
yearFilter.addEventListener('change', function() {
    const year = this.value;
    groupFilter.innerHTML = '<option value="">— Группа —</option>';
    groupFilter.disabled  = true;
    selectAllBtn.disabled = true;

    // Показать/скрыть студентов по курсу
    Array.from(freelancerSelect.options).forEach(opt => {
        opt.style.display = (!year || opt.dataset.year === year) ? '' : 'none';
    });

    if (year && groupsByYear[year]) {
        groupsByYear[year].forEach(function(g) {
            const opt = document.createElement('option');
            opt.value = g.id;
            opt.textContent = g.name;
            groupFilter.appendChild(opt);
        });
        groupFilter.disabled = false;
    }
});

// Группа → фильтр студентов
groupFilter.addEventListener('change', function() {
    const groupId = this.value;
    selectAllBtn.disabled = !groupId;

    Array.from(freelancerSelect.options).forEach(opt => {
        if (!groupId) {
            opt.style.display = opt.dataset.year === yearFilter.value ? '' : 'none';
        } else {
            opt.style.display = opt.dataset.group === groupId ? '' : 'none';
        }
    });
});

// Выбрать всех в группе
selectAllBtn.addEventListener('click', function() {
    const groupId = groupFilter.value;
    Array.from(freelancerSelect.options).forEach(opt => {
        if (opt.dataset.group === groupId && opt.style.display !== 'none') {
            opt.selected = true;
        }
    });
});
</script>
@endsection
