@extends('layouts.app')

@section('title', 'Заявки на роль преподавателя')
@section('page-title', 'Заявки на роль преподавателя')

@section('content')
@if($requests->isEmpty())
    <div class="alert alert-info">Заявок нет.</div>
@else
    @foreach($requests as $req)
    <div class="card border mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
            <strong>{{ $req->user->fullName() }}</strong>
            <span class="text-muted" style="font-size:12px;">{{ $req->user->email }}</span>
            @if($req->status === 'PENDING')
                <span class="badge bg-warning text-dark">На рассмотрении</span>
            @elseif($req->status === 'APPROVED')
                <span class="badge bg-success">Одобрена</span>
            @else
                <span class="badge bg-danger">Отклонена</span>
            @endif
        </div>
        <div class="card-body py-2" style="font-size:13px;">
            @if($req->comment)
                <p class="mb-2"><strong>Комментарий:</strong> {{ $req->comment }}</p>
            @endif
            <p class="mb-2 text-muted">Дата подачи: {{ $req->created_at->format('d.m.Y H:i') }}</p>

            @if($req->status === 'PENDING')
                <form method="POST" action="{{ route('admin.role-requests.review', $req) }}">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <select name="decision" class="form-select form-select-sm" required>
                                <option value="">— Решение —</option>
                                <option value="APPROVED">Одобрить</option>
                                <option value="REJECTED">Отклонить</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="dean_comment" class="form-control form-control-sm"
                                   placeholder="Причина отклонения">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Сохранить</button>
                        </div>
                    </div>
                </form>
            @elseif($req->dean_comment)
                <p class="text-muted mb-0"><strong>Причина отклонения:</strong> {{ $req->dean_comment }}</p>
            @endif
        </div>
    </div>
    @endforeach
@endif
@endsection