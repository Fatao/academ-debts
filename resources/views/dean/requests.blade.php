@extends('layouts.app')

@section('title', 'Заявки преподавателей')
@section('page-title', 'Заявки на изменение пересдач')

@section('content')
@if($requests->isEmpty())
    <div class="alert alert-info">Заявок нет.</div>
@else
    @foreach($requests as $req)
    <div class="card border mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
            <div>
                <strong>{{ $req->retake->discipline->name }}</strong>
                <span class="text-muted ms-2" style="font-size:13px;">
                    {{ $req->retake->start_datetime->format('d.m.Y H:i') }}
                </span>
            </div>
            @if($req->status === 'PENDING')
                <span class="badge bg-warning text-dark">На рассмотрении</span>
            @elseif($req->status === 'APPROVED')
                <span class="badge bg-success">Одобрена</span>
            @else
                <span class="badge bg-danger">Отклонена</span>
            @endif
        </div>
        <div class="card-body py-2" style="font-size:13px;">
            <div class="row">
                <div class="col-md-6">
                    <strong>Преподаватель:</strong> {{ $req->requestedBy->fullName() }}<br>
                    <strong>Дата заявки:</strong> {{ $req->created_at->format('d.m.Y H:i') }}<br>
                    <strong>Причина:</strong> {{ $req->comment }}
                </div>
                <div class="col-md-6">
                    @if($req->new_building || $req->new_room)
                        <strong>Новое место:</strong>
                        корп. {{ $req->new_building ?? '—' }}, ауд. {{ $req->new_room ?? '—' }}<br>
                    @endif
                    @if($req->new_start_datetime)
                        <strong>Новое время:</strong> {{ $req->new_start_datetime->format('d.m.Y H:i') }}<br>
                    @endif
                    @if($req->new_duration_minutes)
                        <strong>Новая продолжительность:</strong> {{ $req->new_duration_minutes }} мин.<br>
                    @endif
                </div>
            </div>

            @if($req->status === 'PENDING')
                <form method="POST" action="{{ route('dean.requests.review', $req) }}" class="mt-3">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <select name="decision" class="form-select form-select-sm" required id="dec{{ $req->id }}">
                                <option value="">— Решение —</option>
                                <option value="APPROVED">Одобрить</option>
                                <option value="REJECTED">Отклонить</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="dean_comment" class="form-control form-control-sm"
                                   placeholder="Причина отклонения (обязательно при отклонении)">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Сохранить</button>
                        </div>
                    </div>
                </form>
            @elseif($req->dean_comment)
                <div class="mt-2 text-muted">
                    <strong>Комментарий деканата:</strong> {{ $req->dean_comment }}
                </div>
            @endif
        </div>
    </div>
    @endforeach
@endif
@endsection