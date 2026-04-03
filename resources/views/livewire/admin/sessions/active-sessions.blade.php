@section('title', '| Panel de Control de Seguridad')

<div wire:poll.15s>
    {{-- Fila de KPIs --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info shadow-sm">
                <div class="inner">
                    <h3>{{ $kpis['total'] }}</h3>
                    <p>Usuarios Totales</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success shadow-sm border-bottom border-success">
                <div class="inner">
                    <h3>{{ $kpis['online'] }}</h3>
                    <p>Sesiones Activas Ahora</p>
                </div>
                <div class="icon"><i class="fas fa-plug"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning shadow-sm">
                <div class="inner">
                    <h3>{{ $kpis['offline'] }}</h3>
                    <p>Usuarios Inactivos</p>
                </div>
                <div class="icon"><i class="fas fa-moon"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger shadow-sm">
                <div class="inner">
                    <h3>{{ $kpis['abandoned'] }}</h3>
                    <p>Sin actividad (>30 días)</p>
                </div>
                <div class="icon"><i class="fas fa-user-clock"></i></div>
            </div>
        </div>
    </div>

    {{-- Barra de Herramientas Superior --}}
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="input-group shadow-sm">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white border-right-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                </div>
                <input type="text" wire:model.live="search" class="form-control border-left-0" placeholder="Buscar por nombre de usuario en tiempo real...">
            </div>
        </div>
    </div>

    <div class="row">
        {{-- COLUMNA IZQUIERDA: SESIONES ACTIVAS --}}
        <div class="col-md-5">
            <div class="card card-outline card-success shadow-sm overflow-hidden">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold">Monitor de Red</h3>
                </div>
                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                    <ul class="list-group list-group-flush">
                        @forelse($active_sessions as $session)
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3 text-success">
                                        <i class="fas fa-circle fa-xs pulse-animation"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <span class="font-weight-bold">{{ $session->name }}</span>
                                            <button wire:click="deleteSession('{{ $session->id }}')" 
                                                    onclick="confirm('¿Terminar sesión del usuario?') || event.stopImmediatePropagation()"
                                                    class="btn btn-xs btn-outline-danger border-0">
                                                <i class="fas fa-times-circle"></i> Terminar
                                            </button>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="fas fa-id-badge mr-1"></i> {{ $session->role_name ?? 'Staff' }} | 
                                            <i class="fas fa-map-marker-alt mr-1"></i> {{ $session->ip_address }}
                                        </div>
                                        <div class="progress mt-2" style="height: 3px;">
                                            <div class="progress-bar bg-success" style="width: 100%"></div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <div class="text-center py-5">
                                <i class="fas fa-ghost fa-3x text-light"></i>
                                <p class="text-muted mt-2">No se encontraron usuarios activos</p>
                            </div>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: HISTORIAL --}}
        <div class="col-md-7">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold">Historial de secciones</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Usuario</th>
                                <th>IP / Acceso</th>
                                <th>Duración / Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($history as $log)
                                <tr>
                                    <td class="align-middle pl-3">
                                        <div class="font-weight-bold">{{ $log->user->name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ Str::limit($log->user_agent, 25) }}</small>
                                    </td>
                                    <td class="align-middle">
                                        <code>{{ $log->ip_address }}</code><br>
                                        <small class="text-success font-weight-bold">{{ \Carbon\Carbon::parse($log->login_at)->format('H:i') }}</small>
                                    </td>
                                    <td class="align-middle">
                                        @if($log->logout_at)
                                            <span class="badge badge-light border">Cerrado</span>
                                            {{-- Carbon 3: diffInMinutes devuelve float; segundo arg = valor absoluto --}}
                                            <small class="d-block text-muted">Sesión de {{ (int) round(\Carbon\Carbon::parse($log->login_at)->diffInMinutes(\Carbon\Carbon::parse($log->logout_at), true)) }} min</small>
                                        @else
                                            <span class="badge badge-success shadow-sm">Conectado</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">
                    <div class="float-right">
                        {{ $history->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .pulse-animation {
            animation: pulse-green 2s infinite;
        }
        @keyframes pulse-green {
            0% { transform: scale(0.95); opacity: 0.7; }
            70% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.7; }
        }
    </style>
</div>