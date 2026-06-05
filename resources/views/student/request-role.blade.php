@extends('layouts.app')

@section('title', 'Заявка на роль преподавателя')
@section('page-title', 'Заявка на роль преподавателя')

@section('content')
<div class="row">
    <div class="col-md-6">

        @if(session('success'))
            <div class="alert alert-success py-2">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger py-2">{{ session('error') }}</div>
        @endif

        @if($existing && $existing->status === 'APPROVED')
            <div class="alert alert-success">Ваша заявка одобрена. Роль преподавателя назначена.</div>
        @elseif($existing && $existing->status === 'PENDING')
            <div class="alert alert-info">
                Ваша заявка находится на рассмотрении. Ожидайте решения деканата.
            </div>
        @elseif($existing && $existing->status === 'REJECTED')
            <div class="alert alert-warning">
                Ваша заявка была отклонена.
                @if($existing->dean_comment)
                    <br><strong>Причина:</strong> {{ $existing->dean_comment }}
                @endif
            </div>
            <p>Вы можете подать новую заявку.</p>
        @endif

        @if(!$existing || $existing->status === 'REJECTED')
            <div class="card border">
                <div class="card-header bg-white">
                    <strong>Подать заявку на роль преподавателя</strong>
                </div>
                <div class="card-body">
                    <p class="text-muted" style="font-size:13px;">
                        Заявка будет рассмотрена деканатом. После одобрения вы получите
                        доступ к функциям преподавателя.
                    </p>
                    <form method="POST" action="{{ route('freelancer.request-role.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Комментарий (необязательно)</label>
                            <textarea name="comment" class="form-control" rows="3"
                                      placeholder="Укажите причину или дополнительную информацию">{{ old('comment') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Подать заявку</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
