@extends('layouts.app')

@section('title', 'Уведомления')
@section('page-title', 'Уведомления')

@section('content')
@if($notifications->isEmpty())
    <div class="alert alert-info">Уведомлений нет.</div>
@else
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th style="width:200px;">Тип</th>
                <th>Сообщение</th>
                <th style="width:140px;">Дата</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notifications as $n)
            <tr {{ !$n->is_read ? 'class=table-light fw-semibold' : '' }}>
                <td>{{ $n->title }}</td>
                <td>{{ $n->message }}</td>
                <td>{{ $n->created_at->format('d.m.Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div>{{ $notifications->links() }}</div>
@endif
@endsection