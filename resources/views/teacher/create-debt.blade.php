@extends('layouts.app')

@section('title', 'Выставить заказ')
@section('page-title', 'Выставить заказ')

@section('content')
<div class="row">
    <div class="col-md-6">
        @if($errors->any())
            <div class="alert alert-danger py-2">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('jobgiver.debts.store') }}">
            @csrf

            <div class="mb-3">
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

            <div class="mb-3">
                <label class="form-label">Фрилансер <span class="text-danger">*</span></label>
                <select name="freelancer_id" class="form-select" required>
                    <option value="">— Выберите фрилансера —</option>
                    @foreach($freelancers as $s)
                        <option value="{{ $s->id }}" {{ old('freelancer_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->fullName() }} {{ $s->group ? '(' . $s->group->name . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Комментарий</label>
                <textarea name="comment" class="form-control" rows="3">{{ old('comment') }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Выставить заказ</button>
                <a href="{{ route('jobgiver.debts') }}" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
    </div>
</div>
@endsection
