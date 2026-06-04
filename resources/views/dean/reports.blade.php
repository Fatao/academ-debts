@extends('layouts.app')

@section('title', 'Отчёты')
@section('page-title', 'Отчёты и выгрузки')

@section('content')
<div class="row">
    <div class="col-md-5">
        <div class="card border">
            <div class="card-header bg-white">
                <strong>Выгрузка пересдач за период</strong>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('dean.reports.export') }}">
                    <div class="mb-3">
                        <label class="form-label">Дата с</label>
                        <input type="date" name="from" class="form-control"
                               value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Дата по</label>
                        <input type="date" name="to" class="form-control"
                               value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Выгрузить CSV</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border">
            <div class="card-header bg-white"><strong>Общая статистика</strong></div>
            <div class="card-body">
                <table class="table table-bordered table-sm mb-0">
                    <tbody>
                        <tr>
                            <td>Всего задолженностей</td>
                            <td class="fw-bold">{{ \App\Models\Debt::count() }}</td>
                        </tr>
                        <tr>
                            <td>Активных задолженностей</td>
                            <td class="fw-bold text-danger">{{ \App\Models\Debt::where('status','DEBT')->count() }}</td>
                        </tr>
                        <tr>
                            <td>Закрытых задолженностей</td>
                            <td class="fw-bold text-success">{{ \App\Models\Debt::where('status','CLOSED')->count() }}</td>
                        </tr>
                        <tr>
                            <td>Всего пересдач</td>
                            <td class="fw-bold">{{ \App\Models\Retake::count() }}</td>
                        </tr>
                        <tr>
                            <td>Завершённых пересдач</td>
                            <td class="fw-bold">{{ \App\Models\Retake::where('status','COMPLETED')->count() }}</td>
                        </tr>
                        <tr>
                            <td>Студентов сдали пересдачу</td>
                            <td class="fw-bold text-success">
                                {{ \Illuminate\Support\Facades\DB::table('retake_students')->where('result_status','PASSED')->count() }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection