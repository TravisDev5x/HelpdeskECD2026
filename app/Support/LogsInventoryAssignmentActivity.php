<?php

namespace App\Support;

use App\Models\InvAsset;
use App\Models\InvMovement;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

/**
 * Registro explícito en activity_log (Spatie) complementario a inv_movements y al log de atributos de InvAsset.
 * Canal: inventory_v2_assignments — permite filtrar sin mezclar con otros cambios del activo.
 */
final class LogsInventoryAssignmentActivity
{
    private const ASSIGNMENT_TYPES = ['CHECKOUT', 'CHECKIN', 'BAJA'];

    public static function record(InvMovement $movement, InvAsset $asset): void
    {
        if (! in_array($movement->type, self::ASSIGNMENT_TYPES, true)) {
            return;
        }

        if (! Schema::hasTable('activity_log') || ! config('activitylog.enabled', true)) {
            return;
        }

        $causer = $movement->admin_id ? User::find($movement->admin_id) : null;

        $messages = [
            'CHECKOUT' => 'Asignación de activo (CHECKOUT)',
            'CHECKIN' => 'Devolución / desasignación (CHECKIN)',
            'BAJA' => 'Baja de activo con liberación de responsable',
        ];

        $description = $messages[$movement->type] ?? ('Movimiento inventario: '.$movement->type);

        $logger = activity('inventory_v2_assignments')
            ->performedOn($asset)
            ->withProperties([
                'inv_movement_id' => $movement->id,
                'movement_type' => $movement->type,
                'responsable_user_id' => $movement->user_id,
                'previous_user_id' => $movement->previous_user_id,
                'reason' => $movement->reason,
                'notes' => $movement->notes,
                'batch_uuid' => $movement->batch_uuid,
                'responsiva_path' => $movement->responsiva_path,
                'movement_date' => $movement->date?->toIso8601String(),
            ])
            ->event('inv_v2_'.$movement->type);

        if ($causer) {
            $logger->causedBy($causer);
        }

        $logger->log($description);
    }
}
