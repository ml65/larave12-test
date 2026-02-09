@extends('adminlte::page')

@section('title', 'Список заявок')

@section('content_header')
    <h1>Список заявок</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Фильтры</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.tickets.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Статус</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">Все</option>
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ (request('status') == $key) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_from">Дата от</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_to">Дата до</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="text" name="email" id="email" class="form-control" value="{{ request('email') }}" placeholder="Email клиента">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="phone">Телефон</label>
                            <input type="text" name="phone" id="phone" class="form-control" value="{{ request('phone') }}" placeholder="Телефон клиента">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">Применить фильтры</button>
                                <a href="{{ route('admin.tickets.index') }}" class="btn btn-secondary">Сбросить</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Заявки ({{ $tickets->count() }})</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Клиент</th>
                        <th>Телефон</th>
                        <th>Email</th>
                        <th>Тема</th>
                        <th>Статус</th>
                        <th>Дата создания</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td>{{ $ticket->id }}</td>
                            <td>{{ $ticket->customer->name ?? '-' }}</td>
                            <td>{{ $ticket->customer->phone ?? '-' }}</td>
                            <td>{{ $ticket->customer->email ?? '-' }}</td>
                            <td>{{ $ticket->subject }}</td>
                            <td>
                                @if($ticket->status === \App\Models\Ticket::STATUS_NEW)
                                    <span class="badge badge-warning">Новая</span>
                                @elseif($ticket->status === \App\Models\Ticket::STATUS_IN_PROGRESS)
                                    <span class="badge badge-info">В работе</span>
                                @else
                                    <span class="badge badge-success">Обработана</span>
                                @endif
                            </td>
                            <td>{{ $ticket->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary">
                                    Просмотр
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Заявки не найдены</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

