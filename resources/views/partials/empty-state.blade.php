{{--
    Estado vacío unificado para tablas/listados.
    Uso: @include('partials.empty-state', ['icon' => 'fa-box-open', 'message' => 'No hay activos registrados.', 'actionLabel' => 'Nuevo Activo', 'actionWire' => 'create'])
    actionLabel y actionWire son opcionales.
--}}
<div class="text-center py-5 px-3">
    <div class="mb-3">
        <i class="fas {{ $icon ?? 'fa-inbox' }} fa-3x text-muted opacity-50"></i>
    </div>
    <p class="text-muted mb-0">{{ $message ?? 'No hay registros para mostrar.' }}</p>
    @if (!empty($actionLabel))
        <div class="mt-3">
            @if (!empty($actionWire))
                <button wire:click="{{ $actionWire }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> {{ $actionLabel }}
                </button>
            @elseif (!empty($actionUrl))
                <a href="{{ $actionUrl }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> {{ $actionLabel }}
                </a>
            @endif
        </div>
    @endif
</div>
