<?php

namespace App\Models;

use App\Observers\UserObserver;
use App\Support\Notifications\InternalNotificationTypeRegistry;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

#[ObservedBy(UserObserver::class)]
class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use SoftDeletes;
    use Auditable;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'ap_paterno',
                'ap_materno',
                'email',
                'usuario',
                'department_id',
                'position_id',
                'campaign_id',
                'area_id',
                'certification',
                'sede',
                'password_expires_at',
                'fecha_baja',
                'motivo_baja',
                'deleted_at',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly([
                'remember_token',
                'updated_at',
                'password',
                'email_verified_at',
                'last_session_id',
                'last_activity',
                'created_at',
            ]);
    }

    protected $fillable = [
        'name', 'ap_paterno', 'ap_materno', 'email', 'password', 'usuario', 'phone',
        'department_id', 'position_id', 'campaign_id', 'area_id', 'certification',
        'sede', 'password_expires_at', 'fecha_baja', 'motivo_baja',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'password_expires_at' => 'datetime',
        'fecha_baja' => 'date',
        'certification' => 'boolean',
    ];

    public function services() { return $this->hasMany(Service::class); }
    public function historicalServices()
{
    
    return $this->hasMany(HistoricalServices::class, 'responsable_id');
}
    public function department() { return $this->belongsTo(Department::class, 'department_id')->withTrashed(); }
    public function products() { return $this->hasMany(Product::class)->withTrashed(); }
    public function maintenances() { return $this->hasMany(Maintenance::class); }
    public function position() { return $this->belongsTo(Position::class, 'position_id')->withTrashed(); }
    public function campaign() { return $this->belongsTo(Campaign::class, 'campaign_id')->withTrashed(); }
    public function area() { return $this->belongsTo(Area::class, 'area_id')->withTrashed(); }
    public function sedes() { return $this->belongsToMany(Sede::class, 'sede_user', 'user_id', 'sede_id')->withTimestamps(); }
    public function assetAssignments() { return $this->hasMany(AssetUser::class, 'user_id'); }
    public function assignmentUser() { return $this->hasMany(Assignment::class, 'employee_id')->withTrashed(); }
    public function assetUser() { return $this->hasOneThrough(Asset::class, AssetUser::class, 'user_id', 'id', 'id', 'asset_id'); }
    public function calendar(){ return $this->hasMany(Calendar::class); }
    public function username() { return 'usuario'; }
    /**
     * Notificaciones internas: tipos sensibles se filtran según permisos modulares (matriz Spatie).
     */
    public function visibleNotifications()
    {
        $q = $this->notifications();
        InternalNotificationTypeRegistry::applyVisibilityByPermissions($q, $this);

        return $q;
    }

    public function visibleUnreadNotifications()
    {
        $q = $this->unreadNotifications();
        InternalNotificationTypeRegistry::applyVisibilityByPermissions($q, $this);

        return $q;
    }
}