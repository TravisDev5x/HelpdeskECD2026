<?php

namespace App\Traits;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public function auditAction(string $action, $before = [], $after = []): Activity
    {
        return activity('user_audit')
            ->causedBy(Auth::user())
            ->performedOn($this)
            ->withProperties([
                'action' => $action,
                'before' => $before,
                'after'  => $after,       
                'role' => Auth::user()->role ?? null,
                'employee_number' => Auth::user()->employee_number ?? null,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'snapshot_victima' => $this->getSnapshot(),
            ])
            ->log($action);
    }

    public function getSnapshot()
    {
       
        return $this->only([
            'department_id', 'position_id', 'campaign_id',
            'name', 'ap_paterno', 'ap_materno', 'usuario',
            'phone', 'email', 'email_verified_at', 'avatar',
            'certification', 'motivo_baja', 'fecha_baja',
            'created_at', 'updated_at', 'deleted_at',
            'password_expires_at', 'role', 'employee_number'
        ]);
    }
}