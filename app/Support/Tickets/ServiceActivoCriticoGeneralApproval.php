<?php

namespace App\Support\Tickets;

use App\Models\HistoricalServices;
use App\Models\Service;

/**
 * Aprobación de ticket de activo crítico por rol General (misma lógica que {@see \App\Http\Controllers\Admin\ServicesController::validation}).
 */
final class ServiceActivoCriticoGeneralApproval
{
    public const FAILURE_ID = 31;

    public static function apply(Service $service): HistoricalServices
    {
        $service->update(['validation' => 1]);

        $data['id'] = null;
        $data['service_id'] = $service->id;
        $data['user_id'] = $service->user_id;
        $data['failure_id'] = $service->failure_id;
        $data['validation'] = 1;

        if (! isset($data['responsable_id'])) {
            $data['responsable_id'] = $service->responsable_id;
        }

        if (! isset($data['solution'])) {
            $data['solution'] = $service->solution;
        }

        if (! isset($data['status'])) {
            $data['status'] = $service->status;
        }

        $data['description'] = $service->description;

        if (! isset($data['observations'])) {
            $data['observations'] = $service->observations;
        }

        return HistoricalServices::create($data);
    }
}
