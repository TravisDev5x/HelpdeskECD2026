{{--
    Modal de confirmación reutilizable para Livewire.
    Requiere en el componente: $showConfirmModal, $confirmTitle, $confirmMessage, $confirmButtonText, $confirmButtonClass
    y métodos: confirmModalConfirm(), confirmModalCancel()
    Uso: @include('partials.confirm-modal', ['confirmButtonText' => 'Eliminar', 'confirmButtonClass' => 'btn-danger'])
    o definir en el componente las variables y usar @include('partials.confirm-modal')
--}}
@if(!empty($showConfirmModal))
<div class="modal fade show d-block modal-livewire-confirm" style="z-index: 1050;" tabindex="-1" role="dialog" wire:key="confirm-modal-overlay">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            @php $isDanger = ($confirmButtonClass ?? '') === 'btn-danger'; @endphp
            <div class="modal-header py-2 {{ $isDanger ? 'bg-danger' : 'bg-warning' }} text-white">
                <h5 class="modal-title font-weight-bold text-sm">
                    <i class="fas fa-exclamation-triangle mr-2"></i>{{ $confirmTitle ?? 'Confirmar' }}
                </h5>
                <button type="button" class="close text-white p-2" wire:click="confirmModalCancel" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body py-3">
                <p class="mb-0">{{ $confirmMessage ?? '¿Está seguro?' }}</p>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" wire:click="confirmModalCancel">
                    Cancelar
                </button>
                <button type="button"
                    class="btn btn-sm {{ $confirmButtonClass ?? 'btn-danger' }} font-weight-bold"
                    wire:click="confirmModalConfirm"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="confirmModalConfirm">
                        <i class="fas fa-check mr-1"></i>{{ $confirmButtonText ?? 'Confirmar' }}
                    </span>
                    <span wire:loading wire:target="confirmModalConfirm">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Procesando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif
