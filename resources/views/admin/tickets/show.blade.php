@extends('adminlte::page')

@section('title', 'Детали заявки #' . $ticket->id)

@section('content_header')
    <h1>Детали заявки #{{ $ticket->id }}</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Информация о заявке</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="200">ID</th>
                            <td>{{ $ticket->id }}</td>
                        </tr>
                        <tr>
                            <th>Тема</th>
                            <td>{{ $ticket->subject }}</td>
                        </tr>
                        <tr>
                            <th>Текст</th>
                            <td>{{ $ticket->text }}</td>
                        </tr>
                        <tr>
                            <th>Статус</th>
                            <td>
                                @if($ticket->status === \App\Models\Ticket::STATUS_NEW)
                                    <span class="badge badge-warning">Новая</span>
                                @elseif($ticket->status === \App\Models\Ticket::STATUS_IN_PROGRESS)
                                    <span class="badge badge-info">В работе</span>
                                @else
                                    <span class="badge badge-success">Обработана</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Дата создания</th>
                            <td>{{ $ticket->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                        @if($ticket->manager_response_date)
                            <tr>
                                <th>Дата ответа менеджера</th>
                                <td>{{ $ticket->manager_response_date->format('d.m.Y H:i') }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Информация о клиенте</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="200">Имя</th>
                            <td>{{ $ticket->customer->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Телефон</th>
                            <td>{{ $ticket->customer->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $ticket->customer->email ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($files->isNotEmpty())
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Прикрепленные файлы</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            @foreach($files as $file)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="fas fa-file"></i> {{ $file->name }}
                                        <small class="text-muted">({{ number_format($file->size / 1024, 2) }} КБ)</small>
                                    </span>
                                    <a href="{{ $file->getUrl() }}" class="btn btn-sm btn-primary" download>
                                        <i class="fas fa-download"></i> Скачать
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Изменить статус</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tickets.updateStatus', $ticket->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="status">Статус</label>
                            <select name="status" id="status" class="form-control" required>
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ $ticket->status === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Сохранить
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <a href="{{ route('admin.tickets.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left"></i> Вернуться к списку
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

