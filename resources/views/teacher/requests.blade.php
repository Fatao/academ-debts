@extends('layouts.app')

@section('title', 'Мои заявки')
@section('page-title', 'Заявки на изменение пересдачи')

@section('content')
<div class="row">
    <div class="col-md-5">
        <h6 class="border-bottom pb-2 mb-3">Новая заявка</h6>
        <form method="POST" action="{{ route('teacher.requests.store') }}">
            @csrf
            @if($errors->any())
                <div class="alert alert-danger py-2">
                    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                </div>
            @endif

            <div class="mb-3">
                <label class="form-label">Пересдача <span class="text-danger">*</span></label>
                <select name="retake_id" class="form-select" required>
                    <option value="">— Выберите пересдачу —</option>
                    @foreach($retakes as $retake)
                        <option value="{{ $retake->id }}">
                            {{ $retake->discipline->name }} — {{ $retake->start_datetime->format('d.m.Y H:i') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <label class="form-label">Новый корпус</label>
                    <input type="text" name="new_building" class="form-control" maxlength="20" value="{{ old('new_building') }}">
                </div>
                <div class="col">
                    <label class="form-label">Новая аудитория</label>
                    <input type="text" name="new_room" class="form-control" maxlength="20" value="{{ old('new_room') }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Новые дата и время</label>
                <input type="datetime-local" name="new_start_datetime" class="form-control" value="{{ old('new_start_datetime') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Новая продолжительность (мин.)</label>
                <input type="number" name="new_duration_minutes" class="form-control" min="15" value="{{ old('new_duration_minutes') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Причина заявки <span class="text-danger">*</span></label>
                <textarea name="comment" class="form-control" rows="3" required>{{ old('comment') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Подать заявку</button>
        </form>
    </div>

    <div class="col-md-7">
        <h6 class="border-bottom pb-2 mb-3">Мои заявки</h6>
        @if($requests->isEmpty())
            <p class="text-muted">Заявок нет.</p>
        @else
            <table class="table table-bordered table-sm table-hover">
                <thead>
                    <tr>
                        <th>Пересдача</th>
                        <th>Статус</th>
                        <th>Комментарий деканата</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                    <tr>
                        <td>
                            {{ $req->retake->discipline->name }}<br>
                            <small class="text-muted">{{ $req->retake->start_datetime->format('d.m.Y H:i') }}</small>
                        </td>
                        <td>
                            @if($req->status === 'PENDING')
                                <span class="badge bg-warning text-dark">На рассмотрении</span>
                            @elseif($req->status === 'APPROVED')
                                <span class="badge bg-success">Одобрена</span>
                            @else
                                <span class="badge bg-danger">Отклонена</span>
                            @endif
                        </td>
                        <td>{{ $req->dean_comment ?? '—' }}</td>
                        <td>{{ $req->created_at->format('d.m.Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection