@extends('admin.layout')

@section('title', '| Servicios a Realizar')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                   {{-- <h1>Gestión de Servicios</h1> --}}
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Tickets</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="callout callout-info py-2 px-3">
            <h5 class="mb-0">{{ $saludo }}, {{ $primerNombre }}</h5>
        </div>

        @include('admin.partials.indicadores')

        @livewire('admin.help-desk.tickets-table')
    </section>

    {{-- Modales de tickets: Livewire (TicketsTable). Sin service-update.js ni modalGuardando en esta pantalla. --}}
@endsection

@push('styles')
    <style>
        .table-hover tbody tr:hover { background-color: rgba(0,0,0,.04); }
        .btn-xs { padding: .125rem .25rem; font-size: .75rem; line-height: 1.5; border-radius: .15rem; }

        .helpdesk-modal-description {
            max-height: 9.5rem;
            overflow-y: auto;
            min-height: 2.5rem;
            background-color: #ffffff !important;
            border-color: #ced4da !important;
            color: #1f2937 !important;
            font-size: .95rem;
            line-height: 1.55;
            white-space: pre-wrap;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, .04);
        }

        body.dark-mode #filters-container.bg-light {
            background-color: #3f474e !important;
            color: #fff;
            border-left: 5px solid #17a2b8;
        }

        body.dark-mode #filters-container select.form-control {
            background-color: #343a40;
            color: #fff;
            border: 1px solid #6c757d;
        }

        body.dark-mode .helpdesk-modal-callout.bg-light {
            background-color: #3f474e !important;
            color: #f8f9fa;
        }

        body.dark-mode .helpdesk-modal-callout .text-dark {
            color: #f8f9fa !important;
        }

        body.dark-mode .helpdesk-modal-description {
            background-color: #2b3138 !important;
            color: #f8fafc !important;
            border-color: #59616a !important;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, .35);
        }

        body.dark-mode .helpdesk-modal-description * {
            color: #f8fafc !important;
        }

        @media (max-width: 576px) {
            .callout { padding: 1rem; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(function() {
            $('body').tooltip({ selector: '[data-toggle="tooltip"]' });
        });

        document.addEventListener('livewire:init', function () {
            Livewire.hook('morph.updated', function () {
                if ($.fn.tooltip) {
                    $('[data-toggle="tooltip"]').tooltip('dispose');
                    $('body').tooltip({ selector: '[data-toggle="tooltip"]' });
                }
            });
        });

        (function openTicketFromNotificationLink() {
            try {
                var params = new URLSearchParams(window.location.search);
                var tid = params.get('id');
                if (!tid) {
                    return;
                }
                tid = String(tid).replace(/\D/g, '');
                var idNum = parseInt(tid, 10);
                if (!idNum) {
                    return;
                }
                setTimeout(function () {
                    if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
                        window.Livewire.dispatch('helpdesk-open-ticket-from-url', { serviceId: idNum });
                    }
                    if (window.history && window.history.replaceState) {
                        var u = new URL(window.location.href);
                        u.searchParams.delete('id');
                        var path = u.pathname + (u.search || '') + (u.hash || '');
                        window.history.replaceState({}, document.title, path);
                    }
                }, 700);
            } catch (e) {}
        })();
    </script>
@endpush
