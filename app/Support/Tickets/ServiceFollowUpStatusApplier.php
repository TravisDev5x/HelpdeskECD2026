<?php

namespace App\Support\Tickets;

use App\Models\Failure;
use App\Models\HistoricalServices;
use App\Models\Service;
use App\Models\User;
use App\Support\Tickets\TicketStatus;
use App\Notifications\InternalUserNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Aplica actualización de ticket con cambio de estatus (seguimiento, cierre, etc.)
 * y registro en historial + notificaciones. Extraído de {@see \App\Http\Controllers\Admin\ServicesController::update}
 * para reutilizar el mismo flujo desde Livewire sin duplicar reglas de negocio.
 */
final class ServiceFollowUpStatusApplier
{
    /**
     * @param  array{status: string, observations: string, solution?: string|null}  $validated
     */
    public static function apply(Service $serviceAux, User $actor, array $validated): HistoricalServices
    {
        $status = $validated['status'];
        $data = [
            'status' => $status,
            'observations' => $validated['observations'],
            'solution' => $validated['solution'] ?? null,
            'responsable_id' => $actor->id,
        ];

        if (TicketStatus::isClosed($status)) {
            $data['fecha_fin'] = Carbon::now();
        }

        if ($status === TicketStatus::SEGUIMIENTO) {
            $data['fecha_seguimiento'] = Carbon::now();
        }

        $oldResponsableId = $serviceAux->responsable_id;
        $wasClosedBefore = TicketStatus::isClosed($serviceAux->status);

        $newResponsableId = isset($data['responsable_id'])
            ? (int) $data['responsable_id']
            : (int) $oldResponsableId;

        $serviceAux->update($data);

        $data['id'] = null;
        $data['service_id'] = $serviceAux->id;
        $data['user_id'] = $serviceAux->user_id;
        $data['failure_id'] = $serviceAux->failure_id;
        $data['sede_id'] = $serviceAux->sede_id;

        if (! isset($data['responsable_id'])) {
            $data['responsable_id'] = $serviceAux->responsable_id;
        }

        if (! isset($data['solution'])) {
            $data['solution'] = $serviceAux->solution;
        }

        if (! isset($data['status'])) {
            $data['status'] = $serviceAux->status;
        }

        $data['description'] = $serviceAux->description;

        if (! isset($data['observations'])) {
            $data['observations'] = $serviceAux->observations;
        }

        $serviceHistorical = HistoricalServices::create($data);

        $failure = Failure::whereId($serviceHistorical->failure_id)->first();
        $user = User::whereId($serviceHistorical->user_id)->first();

        if ($newResponsableId && $newResponsableId !== (int) $oldResponsableId) {
            $assignee = User::find($newResponsableId);
            if ($assignee && $assignee->can('receive internal notification ticket assigned')) {
                $failureLabel = $failure?->name ?? '—';
                try {
                    $assignee->notify(new InternalUserNotification(
                        'Ticket #'.$serviceAux->id.' asignado a ti',
                        'Falla: '.$failureLabel.'. Ya eres responsable de este ticket.',
                        route('home').'?id='.$serviceAux->id,
                        'ticket_assigned'
                    ));
                } catch (\Throwable $e) {
                    Log::warning('No se pudo notificar asignación de ticket', [
                        'service_id' => $serviceAux->id,
                        'assignee_id' => $newResponsableId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $isClosingStatus = TicketStatus::isClosed($status);

        if ($isClosingStatus) {
            if ($user && ! $wasClosedBefore) {
                if ($status === TicketStatus::FINALIZADO) {
                    if ($user->can('receive internal notification ticket resolved')) {
                        $user->notify(new InternalUserNotification(
                            'Ticket #'.$serviceAux->id.' resuelto',
                            'Tu ticket fue marcado como resuelto. Puedes revisar el detalle en inicio.',
                            route('home').'?id='.$serviceAux->id,
                            'ticket_resolved'
                        ));
                    }
                } elseif ($user->can('receive internal notification ticket closed')) {
                    $user->notify(new InternalUserNotification(
                        'Ticket #'.$serviceAux->id.' cerrado',
                        'Tu ticket fue cerrado con estatus: '.$status.'.',
                        route('home').'?id='.$serviceAux->id,
                        'ticket_closed'
                    ));
                }
            }
        }

        return $serviceHistorical;
    }
}
