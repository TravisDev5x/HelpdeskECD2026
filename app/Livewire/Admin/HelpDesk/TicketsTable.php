<?php

namespace App\Livewire\Admin\HelpDesk;

use App\Models\Area;
use App\Models\Failure;
use App\Models\HistoricalServices;
use App\Models\Service;
use App\Models\Sede;
use App\Models\User;
use App\Support\Tickets\HistoricalServiceEventType;
use App\Support\Tickets\ServiceActivoCriticoGeneralApproval;
use App\Support\Tickets\ServiceEscalationApplier;
use App\Support\Tickets\ServiceFollowUpStatusApplier;
use App\Support\Tickets\ServiceObservationAppender;
use App\Support\Tickets\TicketQueryByRole;
use App\Support\Tickets\TicketStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TicketsTable extends Component
{
    use WithPagination;

    public string $search = '';

    /** Persistido en URL (?sede=) para conservar la selección al refrescar. */
    #[Url(as: 'sede', except: '')]
    public string $filtroSede = '';

    #[Url(as: 'area', except: '')]
    public string $filtroArea = '';

    public int $perPage = 10;

    public bool $showHistorialModal = false;

    public ?int $historialServiceId = null;

    /** @var list<array{nombre_r: string, observations: string, solution: string, fecha: string, fecha_fin: string, comentario_cliente: string, fecha_relanzar: string, status: string}> */
    public array $historialRows = [];

    public bool $showObservacionesModal = false;

    public ?int $observacionesServiceId = null;

    public string $observacionesFailureName = '';

    public string $observacionesText = '';

    public bool $showSeguimientoModal = false;

    public ?int $seguimientoServiceId = null;

    public string $seguimientoFailureName = '';

    public string $seguimientoDescription = '';

    /** Snapshot al abrir: si ya hay fecha de seguimiento, solo se permite cerrar (Finalizado) como el modal legado. */
    public bool $seguimientoPhaseComplete = false;

    public string $seguimientoStatus = '';

    public string $seguimientoObservations = '';

    public string $seguimientoSolution = '';

    public bool $showActivoCriticoModal = false;

    public ?int $activoCriticoServiceId = null;

    public string $activoCriticoFailureName = '';

    /** Rol General: valores del formulario legado (Sí=Seguimiento, No=Finalizado); el servidor histórico no ramificaba. */
    public string $activoCriticoGeneralChoice = '';

    /** Admin / Soporte: estatus del seguimiento del activo crítico */
    public string $activoCriticoStaffStatus = '';

    /** Texto único de riesgos / observaciones (equivale a `solution` en el modal Bootstrap). */
    public string $activoCriticoRiesgos = '';

    public bool $showEscalarModal = false;

    public ?int $escalarServiceId = null;

    public ?int $escalarCurrentFailureId = null;

    public string $escalarCurrentFailureLabel = '';

    public string $escalarCurrentAreaLabel = '';

    public ?int $escalarAreaId = null;

    public ?int $escalarNewFailureId = null;

    public string $escalarReason = '';

    protected $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        abort_unless(Auth::user()?->can('read services'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroSede(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroArea(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    /** Pausa auto-refresh (wire:poll) para no re-renderizar la tabla mientras un modal tiene el foco. */
    public function anyTicketModalOpen(): bool
    {
        return $this->showHistorialModal
            || $this->showObservacionesModal
            || $this->showSeguimientoModal
            || $this->showActivoCriticoModal
            || $this->showEscalarModal;
    }

    #[On('helpdesk-open-ticket-from-url')]
    public function openTicketFromUrl(int $serviceId): void
    {
        $this->openSeguimientoModal($serviceId);
    }

    public function openSeguimientoModal(int $serviceId): void
    {
        $service = Service::query()
            ->with('failure')
            ->findOrFail($serviceId);

        abort_unless(Auth::user()?->can('view', $service), 403);

        $this->seguimientoServiceId = $service->id;
        $this->seguimientoFailureName = $service->failure?->name ?? '—';
        $desc = trim((string) ($service->description ?? ''));
        $this->seguimientoDescription = $desc !== '' ? $desc : 'Sin descripción registrada.';
        $this->seguimientoPhaseComplete = $service->fecha_seguimiento !== null;
        $this->seguimientoStatus = '';
        $this->seguimientoSolution = '';
        $this->seguimientoObservations = $this->seguimientoPhaseComplete
            ? (string) ($service->observations ?? '')
            : '';
        $this->resetValidation([
            'seguimientoStatus',
            'seguimientoObservations',
            'seguimientoSolution',
        ]);
        $this->showSeguimientoModal = true;
    }

    public function closeSeguimientoModal(): void
    {
        $this->showSeguimientoModal = false;
        $this->seguimientoServiceId = null;
        $this->seguimientoFailureName = '';
        $this->seguimientoDescription = '';
        $this->seguimientoPhaseComplete = false;
        $this->seguimientoStatus = '';
        $this->seguimientoObservations = '';
        $this->seguimientoSolution = '';
        $this->resetValidation();
    }

    public function saveSeguimiento(): void
    {
        if ($this->seguimientoServiceId === null) {
            return;
        }

        $service = Service::query()->findOrFail($this->seguimientoServiceId);
        abort_unless(Auth::user()?->can('update', $service), 403);

        $allowedStatuses = $this->seguimientoPhaseComplete
            ? [TicketStatus::FINALIZADO]
            : [TicketStatus::SEGUIMIENTO, TicketStatus::TICKET_ERRONEO];

        $rules = [
            'seguimientoStatus' => ['required', 'string', Rule::in($allowedStatuses)],
            'seguimientoObservations' => ['required', 'string', 'max:65535'],
        ];

        $requiresSolution = $this->seguimientoPhaseComplete
            || $this->seguimientoStatus === TicketStatus::FINALIZADO;

        if ($requiresSolution) {
            $rules['seguimientoSolution'] = ['required', 'string', 'max:65535'];
        } else {
            $rules['seguimientoSolution'] = ['nullable', 'string', 'max:65535'];
        }

        $this->validate($rules);

        $actor = Auth::user();
        if (! $actor instanceof User) {
            abort(403);
        }

        $payload = [
            'status' => $this->seguimientoStatus,
            'observations' => $this->seguimientoObservations,
            'solution' => $this->seguimientoSolution !== '' ? $this->seguimientoSolution : null,
        ];

        ServiceFollowUpStatusApplier::apply($service, $actor, $payload);

        $this->closeSeguimientoModal();
        session()->flash('flash', 'Seguimiento guardado.');
    }

    public function openActivoCriticoModal(int $serviceId): void
    {
        $service = Service::query()->with('failure')->findOrFail($serviceId);

        abort_unless(Auth::user()?->can('view', $service), 403);
        abort_unless((int) $service->failure_id === ServiceActivoCriticoGeneralApproval::FAILURE_ID, 403);
        abort_unless($this->userMayActOnActivoCritico(Auth::user()), 403);
        abort_unless((int) ($service->validation ?? 0) !== 1, 403);

        $this->activoCriticoServiceId = $service->id;
        $this->activoCriticoFailureName = $service->failure?->name ?? '—';
        $this->activoCriticoGeneralChoice = '';
        $this->activoCriticoStaffStatus = '';
        $this->activoCriticoRiesgos = '';
        $this->resetValidation([
            'activoCriticoGeneralChoice',
            'activoCriticoStaffStatus',
            'activoCriticoRiesgos',
        ]);
        $this->showActivoCriticoModal = true;
    }

    public function closeActivoCriticoModal(): void
    {
        $this->showActivoCriticoModal = false;
        $this->activoCriticoServiceId = null;
        $this->activoCriticoFailureName = '';
        $this->activoCriticoGeneralChoice = '';
        $this->activoCriticoStaffStatus = '';
        $this->activoCriticoRiesgos = '';
        $this->resetValidation();
    }

    public function saveActivoCritico(): void
    {
        if ($this->activoCriticoServiceId === null) {
            return;
        }

        $service = Service::query()->findOrFail($this->activoCriticoServiceId);
        abort_unless(Auth::user()?->can('update', $service), 403);
        abort_unless((int) $service->failure_id === ServiceActivoCriticoGeneralApproval::FAILURE_ID, 403);
        abort_unless($this->userMayActOnActivoCritico(Auth::user()), 403);

        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        if ($user->hasRole('General')) {
            $this->validate([
                'activoCriticoGeneralChoice' => ['required', 'string', Rule::in([TicketStatus::SEGUIMIENTO, TicketStatus::FINALIZADO])],
            ]);

            ServiceActivoCriticoGeneralApproval::apply($service);
            $this->closeActivoCriticoModal();
            session()->flash('flash', 'Validación de activo crítico registrada.');

            return;
        }

        $this->validate([
            'activoCriticoStaffStatus' => ['required', 'string', Rule::in([TicketStatus::SEGUIMIENTO, TicketStatus::FINALIZADO])],
            'activoCriticoRiesgos' => ['required', 'string', 'max:65535'],
        ]);

        $observations = $this->activoCriticoRiesgos;
        $solution = $this->activoCriticoStaffStatus === TicketStatus::FINALIZADO
            ? $this->activoCriticoRiesgos
            : null;

        ServiceFollowUpStatusApplier::apply($service, $user, [
            'status' => $this->activoCriticoStaffStatus,
            'observations' => $observations,
            'solution' => $solution,
        ]);

        $this->closeActivoCriticoModal();
        session()->flash('flash', 'Seguimiento de activo crítico guardado.');
    }

    private function userMayActOnActivoCritico(?User $user): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        $names = $user->getRoleNames()->values()->all();

        return count(array_intersect($names, ['Admin', 'Soporte', 'General'])) > 0;
    }

    public function openHistorialModal(int $serviceId): void
    {
        $service = Service::query()->findOrFail($serviceId);

        abort_unless(Auth::user()?->can('view', $service), 403);

        $this->historialServiceId = $serviceId;
        $this->historialRows = $this->loadHistorialRows($serviceId);
        $this->showHistorialModal = true;
    }

    public function openEscalarModal(int $serviceId): void
    {
        $service = Service::query()->with(['failure.area'])->findOrFail($serviceId);

        abort_unless(Auth::user()?->can('escalate', $service), 403);

        $this->escalarServiceId = $service->id;
        $this->escalarCurrentFailureId = $service->failure_id !== null ? (int) $service->failure_id : null;
        $this->escalarCurrentFailureLabel = $service->failure?->name ?? '—';
        $this->escalarCurrentAreaLabel = $service->failure?->area?->name ?? '—';
        $this->escalarAreaId = null;
        $this->escalarNewFailureId = null;
        $this->escalarReason = '';
        $this->resetValidation([
            'escalarAreaId',
            'escalarNewFailureId',
            'escalarReason',
        ]);
        $this->showEscalarModal = true;
    }

    public function closeEscalarModal(): void
    {
        $this->showEscalarModal = false;
        $this->escalarServiceId = null;
        $this->escalarCurrentFailureId = null;
        $this->escalarCurrentFailureLabel = '';
        $this->escalarCurrentAreaLabel = '';
        $this->escalarAreaId = null;
        $this->escalarNewFailureId = null;
        $this->escalarReason = '';
        $this->resetValidation();
    }

    /**
     * Al cambiar el área destino se limpia el tipo de falla seleccionado.
     */
    public function updatedEscalarAreaId(mixed $value): void
    {
        $this->escalarNewFailureId = null;
        $this->resetValidation(['escalarNewFailureId']);
    }

    public function saveEscalacion(): void
    {
        if ($this->escalarServiceId === null) {
            return;
        }

        $service = Service::query()->findOrFail($this->escalarServiceId);
        abort_unless(Auth::user()?->can('escalate', $service), 403);

        $this->validate([
            'escalarAreaId' => ['required', 'integer', 'exists:areas,id'],
            'escalarNewFailureId' => ['required', 'integer', Rule::exists('failures', 'id')->where(fn ($q) => $q->where('area_id', $this->escalarAreaId))],
            'escalarReason' => ['required', 'string', 'max:2000'],
        ], [], [
            'escalarAreaId' => 'área destino',
            'escalarNewFailureId' => 'tipo de falla',
            'escalarReason' => 'motivo',
        ]);

        $actor = Auth::user();
        if (! $actor instanceof User) {
            abort(403);
        }

        try {
            ServiceEscalationApplier::apply($service, $actor, (int) $this->escalarNewFailureId, $this->escalarReason);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $key => $messages) {
                $livewireKey = match ($key) {
                    'failure_id' => 'escalarNewFailureId',
                    'reason' => 'escalarReason',
                    default => $key,
                };
                foreach ($messages as $msg) {
                    $this->addError($livewireKey, $msg);
                }
            }

            return;
        }

        $this->closeEscalarModal();
        session()->flash('flash', 'Ticket escalado al área y tipo de falla seleccionados.');
    }

    public function closeHistorialModal(): void
    {
        $this->showHistorialModal = false;
        $this->historialServiceId = null;
        $this->historialRows = [];
    }

    public function openObservacionesModal(int $serviceId): void
    {
        $service = Service::query()->with('failure')->findOrFail($serviceId);

        abort_unless(Auth::user()?->can('view', $service), 403);
        abort_unless(Auth::user()?->can('update', $service), 403);

        $this->observacionesServiceId = $serviceId;
        $this->observacionesFailureName = $service->failure?->name ?? '—';
        $this->observacionesText = (string) ($service->observations ?? '');
        $this->showObservacionesModal = true;
    }

    public function closeObservacionesModal(): void
    {
        $this->showObservacionesModal = false;
        $this->observacionesServiceId = null;
        $this->observacionesFailureName = '';
        $this->observacionesText = '';
        $this->resetValidation();
    }

    public function saveObservaciones(): void
    {
        if ($this->observacionesServiceId === null) {
            return;
        }

        $validated = $this->validate([
            'observacionesText' => ['required', 'string', 'max:65535'],
        ]);

        $service = Service::query()->findOrFail($this->observacionesServiceId);
        abort_unless(Auth::user()?->can('update', $service), 403);

        ServiceObservationAppender::run($service, $validated['observacionesText']);

        $this->closeObservacionesModal();
        session()->flash('flash', 'Observaciones guardadas.');
    }

    /**
     * @return list<array{
     *   nombre_r: string,
     *   observations: string,
     *   solution: string,
     *   fecha: string,
     *   fecha_fin: string,
     *   comentario_cliente: string,
     *   fecha_relanzar: string,
     *   status: string,
     *   event_type: ?string,
     *   escalation_from: string,
     *   escalation_to: string,
     *   escalation_reason: string
     * }>
     */
    private function loadHistorialRows(int $serviceId): array
    {
        return HistoricalServices::query()
            ->where('service_id', $serviceId)
            ->with([
                'responsable:id,name,ap_paterno,ap_materno',
                'failure' => static fn ($q) => $q->with(['area']),
                'previousFailure' => static fn ($q) => $q->with(['area']),
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(function (HistoricalServices $item): array {
                $nombre = trim(implode(' ', array_filter([
                    $item->responsable?->name,
                    $item->responsable?->ap_paterno,
                    $item->responsable?->ap_materno,
                ])));

                $isEscalation = $item->event_type === HistoricalServiceEventType::ESCALATION;
                $prevId = $item->previous_failure_id !== null ? (int) $item->previous_failure_id : null;
                $failId = $item->failure_id !== null ? (int) $item->failure_id : null;

                return [
                    'nombre_r' => $nombre !== '' ? $nombre : 'Sin responsable',
                    'observations' => $item->observations ?: 'Sin observaciones',
                    'solution' => $item->solution ?: 'Sin solución',
                    'fecha' => $item->created_at ? Carbon::parse($item->created_at)->format('Y-m-d H:i:s') : '',
                    'fecha_fin' => $item->fecha_fin ? Carbon::parse($item->fecha_fin)->format('Y-m-d H:i:s') : '',
                    'comentario_cliente' => $item->comentario_cliente ?? '',
                    'fecha_relanzar' => $item->fecha_relanzar ? Carbon::parse($item->fecha_relanzar)->format('Y-m-d H:i:s') : '',
                    'status' => (string) $item->status,
                    'event_type' => $item->event_type,
                    'escalation_from' => $isEscalation ? $this->formatHistoricalFailureAreaLabel($item->previousFailure, $prevId) : '',
                    'escalation_to' => $isEscalation ? $this->formatHistoricalFailureAreaLabel($item->failure, $failId) : '',
                    'escalation_reason' => $isEscalation ? (string) ($item->escalation_reason ?? '') : '',
                ];
            })
            ->values()
            ->all();
    }

    private function formatHistoricalFailureAreaLabel(?Failure $failure, ?int $fallbackId): string
    {
        if ($failure !== null) {
            $area = $failure->relationLoaded('area') ? $failure->area?->name : null;
            $name = $failure->name ?? '—';

            return ($area !== null && $area !== '') ? $area.' · '.$name : $name;
        }

        if ($fallbackId !== null) {
            return 'Falla #'.$fallbackId.' (catálogo no disponible)';
        }

        return '—';
    }

    public function render(): View
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        $query = TicketQueryByRole::queryWithListRelations()
            ->whereNotIn('status', TicketStatus::closed());
        TicketQueryByRole::applyUserVisibilityScope($query, $user);

        $sedeId = trim($this->filtroSede);
        if ($sedeId !== '' && ctype_digit($sedeId)) {
            $query->where('sede_id', (int) $sedeId);
        }

        if ($this->filtroArea !== '') {
            $query->whereHas('failure.area', function ($q): void {
                $q->where('areas.name', $this->filtroArea);
            });
        }

        $term = trim($this->search);
        if ($term !== '') {
            $query->where(function ($q) use ($term): void {
                if (ctype_digit($term)) {
                    $q->where('services.id', (int) $term);
                } else {
                    $like = '%'.$term.'%';
                    $q->where('services.description', 'like', $like)
                        ->orWhere('services.status', 'like', $like)
                        ->orWhere('services.solution', 'like', $like)
                        ->orWhere('services.observations', 'like', $like)
                        ->orWhereHas('failure', fn ($fq) => $fq->where('name', 'like', $like))
                        ->orWhereHas('user', function ($uq) use ($like): void {
                            $uq->where('name', 'like', $like)
                                ->orWhere('ap_paterno', 'like', $like)
                                ->orWhere('ap_materno', 'like', $like);
                        });
                }
            });
        }

        $tickets = $query->orderBy('services.id')->paginate($this->perPage);

        $authRoleNames = $user->getRoleNames()->values()->all();

        $escalarFailures = collect();
        if ($this->showEscalarModal && $this->escalarAreaId !== null) {
            $q = Failure::query()->where('area_id', $this->escalarAreaId)->orderBy('name');
            if ($this->escalarCurrentFailureId !== null) {
                $q->where('id', '!=', $this->escalarCurrentFailureId);
            }
            $escalarFailures = $q->get();
        }

        return view('livewire.admin.help-desk.tickets-table', [
            'tickets' => $tickets,
            'authRoleNames' => $authRoleNames,
            'sedes' => Sede::query()->orderBy('sede')->get(),
            'areas' => Area::query()->orderBy('name')->get(),
            'escalarFailures' => $escalarFailures,
            'helpdeskIsGeneralUser' => $user->hasRole('General'),
        ]);
    }
}
