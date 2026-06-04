@extends('layouts.app')

@section('title', 'Дисциплины')
@section('page-title', 'Управление дисциплинами')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card border mb-4">
            <div class="card-header bg-white"><strong>Добавить дисциплину</strong></div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger py-2">
                        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                    </div>
                @endif
                <form method="POST" action="{{ route('admin.disciplines.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Название <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Код</label>
                        <input type="text" name="code" class="form-control"
                               maxlength="50" value="{{ old('code') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Преподаватели</label>
                        <select name="teacher_ids[]" class="form-select" multiple size="5">
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->fullName() }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Ctrl — несколько преподавателей.</div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Добавить</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Код</th>
                    <th>Название</th>
                    <th>Преподавателей</th>
                    <th>Задолженностей</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($disciplines as $i => $d)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $d->code ?? '—' }}</td>
                    <td>{{ $d->name }}</td>
                    <td>{{ $d->teachers_count }}</td>
                    <td>{{ $d->debts_count }}</td>
                    <td>
                        @if($d->debts_count == 0)
                            <form method="POST" action="{{ route('admin.disciplines.delete', $d) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Удалить дисциплину «{{ $d->name }}»?')">
                                    Удалить
                                </button>
                            </form>
                        @else
                            <span class="text-muted" style="font-size:12px;">Есть долги</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection