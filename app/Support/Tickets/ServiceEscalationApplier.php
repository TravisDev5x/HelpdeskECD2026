<?php

namespace App\Support\Tickets;

use App\Models\Failure;
use App\Models\HistoricalServices;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Cambia el tipo de falla (y con ello el área de servicio) de un ticket y deja
 * una fila en {@see HistoricalServices}, sin modificar la descripción ni las
 * observaciones del ticket en vivo (el motivo queda en la instantánea de historial).
 *
 * La autorización (policy escalate) debe aplicarla el llamador.
 */
final class ServiceEscalationApplier
{
    /**
     * @throws ValidationException
     */
    public static function apply(Service $service, User $actor, int $newFailureId, string $reason): HistoricalServices
    {
        $reasonTrim = trim($reason);

        if ($reasonTrim === '') {
            throw ValidationException::withMessages([
                'reason' => ['Indica el motivo del escalado.'],
            ]);
        }

        if (Str::length($reasonTrim) > 2000) {
            throw ValidationException::withMessages([
                'reason' => ['El motivo no puede superar 2000 caracteres.'],
            ]);
        }

        if (TicketStatus::isClosed((string) $service->status)) {
            throw ValidationException::withMessages([
                'failure_id' => ['No se puede escalar un ticket finalizado o erróneo.'],
            ]);
        }

        $service->loadMissing('failure.area');

        $oldFailureId = (int) $service->failure_id;

        if ($newFailureId === $oldFailureId) {
            throw ValidationException::withMessages([
                'failure_id' => ['Selecciona un tipo de falla distinto al actual.'],
            ]);
        }

        $newFailure = Failure::query()->whereKey($newFailureId)->first();

        if ($newFailure === null) {
            throw ValidationException::withMessages([
                'failure_id' => ['El tipo de falla indicado no existe.'],
            ]);
        }

        $oldAreaId = $service->failure?->area_id !== null ? (int) $service->failure->area_id : null;
        $newAreaId = $newFailure->area_id !== null ? (int) $newFailure->area_id : null;

        return DB::transaction(function () use ($service, $actor, $newFailureId, $reasonTrim, $oldAreaId, $newAreaId, $oldFailureId): HistoricalServices {
            $update = ['failure_id' => $newFailureId];

            if ($oldAreaId !== $newAreaId) {
                $update['responsable_id'] = null;
            }

            $service->update($update);
            $service->refresh();

            $actorLabel = trim(implode(' ', array_filter([
                $actor->name,
                $actor->ap_paterno,
                $actor->ap_materno,
            ])));
            if ($actorLabel === '') {
                $actorLabel = 'Usuario #'.$actor->id;
            }

            $escalationNote = sprintf(
                '[Escalado %s — %s] %s',
                now()->format('Y-m-d H:i'),
                $actorLabel,
                $reasonTrim
            );

            $currentObs = trim((string) ($service->observations ?? ''));
            $snapshotObservations = $currentObs !== '' ? $escalationNote."\n\n".$currentObs : $escalationNote;

            $data = [
                'service_id' => $service->id,
                'user_id' => $service->user_id,
                'responsable_id' => $service->responsable_id,
                'failure_id' => $service->failure_id,
                'event_type' => HistoricalServiceEventType::ESCALATION,
                'previous_failure_id' => $oldFailureId,
                'escalation_reason' => $reasonTrim,
                'description' => $service->description,
                'solution' => $service->solution,
                'observations' => $snapshotObservations,
                'status' => $service->status,
                'fecha_fin' => $service->fecha_fin,
                'sede_id' => $service->sede_id,
                'fecha_seguimiento' => $service->fecha_seguimiento,
                'comentario_cliente' => $service->comentario_cliente,
                'fecha_relanzar' => $service->fecha_relanzar,
            ];

            return HistoricalServices::create($data);
        });
    }
}
