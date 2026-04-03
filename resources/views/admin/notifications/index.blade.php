@extends('admin.layout')

@section('title', '| Notificaciones')

@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Notificaciones</h1>
            </div>
            <div class="col-sm-6 text-right">
                <form action="{{ route('admin.notifications.index') }}" method="GET" class="d-inline mr-2">
                    <select name="type" class="custom-select custom-select-sm" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        @can('receive internal notification ticket created')
                        <option value="ticket_created" @selected(($type ?? '') === 'ticket_created')>Ticket creado</option>
                        @endcan
                        @can('receive internal notification ticket assigned')
                        <option value="ticket_assigned" @selected(($type ?? '') === 'ticket_assigned')>Ticket asignado</option>
                        @endcan
                        @can('receive internal notification ticket resolved')
                        <option value="ticket_resolved" @selected(($type ?? '') === 'ticket_resolved')>Ticket resuelto</option>
                        @endcan
                        @can('receive internal notification ticket closed')
                        <option value="ticket_closed" @selected(($type ?? '') === 'ticket_closed')>Ticket cerrado (otro)</option>
                        @endcan
                        @can('receive internal notification user login')
                        <option value="user_login" @selected(($type ?? '') === 'user_login')>Inicio de sesión</option>
                        @endcan
                        @can('receive internal notification password support')
                        <option value="password_support_request" @selected(($type ?? '') === 'password_support_request')>Solicitud ayuda acceso</option>
                        @endcan
                        @can('receive internal notification user missing email')
                        <option value="user_missing_email" @selected(($type ?? '') === 'user_missing_email')>Usuario sin correo</option>
                        @endcan
                        @can('receive internal notification password expiring soon')
                        <option value="password_expiring_soon" @selected(($type ?? '') === 'password_expiring_soon')>Contraseña por vencer</option>
                        @endcan
                        @can('receive internal notification info')
                        <option value="info" @selected(($type ?? '') === 'info')>Genérico (info)</option>
                        @endcan
                    </select>
                </form>
                <form action="{{ route('admin.notifications.read-all') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-check-double mr-1"></i> Marcar todas como leídas
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <ul class="list-group list-group-flush">
                @forelse($notifications as $notification)
                    @php
                        $raw = $notification->data ?? [];
                        $data = is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);
                        $title = $data['title'] ?? 'Notificación';
                        $message = $data['message'] ?? '';
                        $url = $data['url'] ?? null;
                    @endphp
                    <li class="list-group-item {{ $notification->read_at ? '' : 'font-weight-bold' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div>{{ $title }}</div>
                                @if($message)
                                    <small class="text-muted d-block">{{ $message }}</small>
                                @endif
                                <small class="text-muted">{{ $notification->created_at?->diffForHumans() }}</small>
                            </div>
                            <div class="text-right">
                                @if($url)
                                    <a href="{{ route('admin.notifications.open', $notification->id) }}" class="btn btn-xs btn-outline-info">Abrir</a>
                                @endif
                                @if(!$notification->read_at)
                                    <form action="{{ route('admin.notifications.read', $notification->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-outline-secondary">Leída</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="list-group-item text-muted text-center py-4">No hay notificaciones.</li>
                @endforelse
            </ul>
        </div>
        <div class="card-footer">
            {{ $notifications->appends(['type' => $type ?? null])->links() }}
        </div>
    </div>
@endsection

