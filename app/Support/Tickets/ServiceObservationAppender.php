<?php

namespace App\Support\Tickets;

use App\Models\HistoricalServices;
use App\Models\Service;

/**
 * Actualiza solo observaciones del ticket y registra una fila en historical_services
 * (misma semántica que ServicesController::update cuando no se envía status).
 */
final class ServiceObservationAppender
{
    public static function run(Service $service, string $observations): HistoricalServices
    {
        $service->update([
            'observations' => $observations,
        ]);

        $service->refresh();

        $data = [
            'service_id' => $service->id,
            'user_id' => $service->user_id,
            'responsable_id' => $service->responsable_id,
            'failure_id' => $service->failure_id,
            'description' => $service->description,
            'solution' => $service->solution,
            'observations' => $service->observations,
            'status' => $service->status,
            'fecha_fin' => $service->fecha_fin,
            'sede_id' => $service->sede_id,
            'fecha_seguimiento' => $service->fecha_seguimiento,
            'comentario_cliente' => $service->comentario_cliente,
            'fecha_relanzar' => $service->fecha_relanzar,
        ];

        return HistoricalServices::create($data);
    }
}
