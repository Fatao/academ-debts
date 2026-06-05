@extends('layouts.app')

@section('title', 'Импорт данных')
@section('page-title', 'Импорт данных из внешней системы')

@section('content')

@if(session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
@endif

@if(session('import_errors') && count(session('import_errors')))
    <div class="alert alert-warning py-2">
        <strong>Предупреждения:</strong>
        <ul class="mb-0 mt-1 ps-3">
            @foreach(session('import_errors') as $e)
                <li style="font-size:13px;">{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">

    {{-- Импорт студентов --}}
    <div class="col-md-6">
        <div class="card border h-100">
            <div class="card-header bg-white">
                <strong>Импорт студентов</strong>
            </div>
            <div class="card-body">
                <p class="text-muted" style="font-size:13px;">
                    Загрузите CSV-файл со списком студентов. Новые студенты будут
                    добавлены в систему с паролем по умолчанию <code>student123</code>.
                    Существующие аккаунты не изменяются.
                </p>

                <div class="mb-3 p-2 border rounded bg-light" style="font-size:12px;">
                    <strong>Формат файла (разделитель — точка с запятой):</strong><br>
                    <code>Фамилия;Имя;Отчество;Email;Группа</code><br><br>
                    <strong>Пример:</strong><br>
                    <code>Иванов;Иван;Иванович;ivanov@edu.ugrasu.ru;ИВТ41Б</code><br>
                    <code>Петрова;Мария;Сергеевна;petrova@edu.ugrasu.ru;ПИ41Б</code>
                </div>

                <form method="POST" action="{{ route('dean.import.students') }}"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">CSV-файл <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Импортировать студентов</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Импорт задолженностей --}}
    <div class="col-md-6">
        <div class="card border h-100">
            <div class="card-header bg-white">
                <strong>Импорт задолженностей</strong>
            </div>
            <div class="card-body">
                <p class="text-muted" style="font-size:13px;">
                    Загрузите CSV-файл со списком задолженностей. Студенты и преподаватели
                    должны уже существовать в системе.
                </p>

                <div class="mb-3 p-2 border rounded bg-light" style="font-size:12px;">
                    <strong>Формат файла (разделитель — точка с запятой):</strong><br>
                    <code>Email студента;Код дисциплины;Название дисциплины;Email преподавателя</code><br><br>
                    <strong>Пример:</strong><br>
                    <code>ivanov@edu.ugrasu.ru;МАТ101;Высшая математика;smirnov@edu.ugrasu.ru</code><br>
                    <code>petrova@edu.ugrasu.ru;ПРО201;Программирование;kozlov@edu.ugrasu.ru</code>
                </div>

                <form method="POST" action="{{ route('dean.import.debts') }}"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">CSV-файл <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Импортировать задолженности</button>
                </form>
            </div>
        </div>
    </div>

</div>

{{-- Шаблоны для скачивания --}}
<div class="mt-4">
    <h6 class="border-bottom pb-2 mb-3">Шаблоны файлов для заполнения</h6>
    <div class="d-flex gap-3">
        <a href="{{ route('dean.import.template', 'students') }}" class="btn btn-outline-secondary btn-sm">
            Скачать шаблон студентов
        </a>
        <a href="{{ route('dean.import.template', 'debts') }}" class="btn btn-outline-secondary btn-sm">
            Скачать шаблон задолженностей
        </a>
    </div>
</div>

@endsection