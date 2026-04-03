<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Department;
use App\Models\Position;

class AuditTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // --- FILTROS Y PAGINACIÓN ---
    #[Url(except: '')]
    public $search = '';
    public $perPage = 10;
    #[Url(except: '')]
    public $dateFrom = null;
    #[Url(except: '')]
    public $dateTo = null;

    // --- VARIABLES DE ESTADÍSTICAS (KPI CARDS) ---
    public $stats = [
        'created' => 0,
        'deleted' => 0,
        'updated' => 0,
        'restored' => 0,
    ];

    // --- MODAL Y DETALLES ---
    public $selectedLog = null;

    // --- CACHÉ Y CONFIGURACIÓN ---
    private $catalogs = [];
    private const HIDDEN_FIELDS = [
        'id', 'email_verified_at', 'certification', 'avatar', 'profile_photo_path', 
        'password', 'password_expires_at', 'remember_token', 
        'two_factor_recovery_codes', 'two_factor_secret', 'current_team_id',
        'updated_at', 'created_at', 'deleted_at'
    ];

    public function mount()
    {
        abort_unless(Auth::check() && Auth::user()->hasAnyRole(['Admin', 'Soporte']), 403);

        $this->calculateStats();
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function updatedDateFrom() { $this->resetPage(); $this->calculateStats(); }
    public function updatedDateTo() { $this->resetPage(); $this->calculateStats(); }

    // --- LÓGICA DE ESTADÍSTICAS ---
    public function calculateStats()
    {
        $queryBase = Activity::query()
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo));

        // 1. Bajas
        $this->stats['deleted'] = (clone $queryBase)->whereIn('description', ['User Deleted', 'deleted'])->count();
        
        // 2. Altas
        $this->stats['created'] = (clone $queryBase)->whereIn('description', ['User Created', 'created'])->count();
        
        // 3. Restaurados (NUEVO)
        $this->stats['restored'] = (clone $queryBase)->where(function($q) {
            $q->where('description', 'restored')
              ->orWhere('description', 'like', '%Restored%');
        })->count();

        // 4. Ediciones (Todo lo que no sea lo anterior)
        $this->stats['updated'] = (clone $queryBase)->where(function($q) {
            $q->whereNotIn('description', ['User Deleted', 'deleted', 'User Created', 'created', 'restored'])
              ->where('description', 'not like', '%Restored%');
        })->count();
    }

    public function humanizeAction($log)
    {
        $desc = $log->description;
        $props = $log->properties;

        if ($desc === 'User Deleted' || $desc === 'deleted') {
            $motivo = $props['attributes']['motivo_baja'] ?? 'No especificado';
            return [
                'badge' => 'BAJA',
                'class' => 'danger',
                'icon' => 'fas fa-user-times',
                'message' => 'Baja de personal.',
                'detail_label' => 'Motivo',
                'detail_value' => $motivo
            ];
        }
        if ($desc === 'User Created' || $desc === 'created') {
            return ['badge' => 'ALTA', 'class' => 'success', 'icon' => 'fas fa-user-plus', 'message' => 'Nueva contratación / Alta en sistema'];
        }
        if ($desc === 'Password Changed') {
            return ['badge' => 'SEGURIDAD', 'class' => 'warning', 'icon' => 'fas fa-key', 'message' => 'Cambio de contraseña'];
        }
        if ($desc === 'restored' || str_contains($desc, 'Restored')) {
            return ['badge' => 'REINGRESO', 'class' => 'purple', 'icon' => 'fas fa-trash-restore', 'message' => 'Restauración de usuario eliminado'];
        }
        return ['badge' => 'EDICIÓN', 'class' => 'secondary', 'icon' => 'fas fa-pen', 'message' => 'Actualización de datos'];
    }

    // --- CATÁLOGOS ---
    private function loadCatalogs()
    {
        if (empty($this->catalogs)) {
            $this->catalogs['campaigns'] = Campaign::pluck('name', 'id')->toArray();
            $this->catalogs['departments'] = Department::pluck('name', 'id')->toArray();
            $this->catalogs['positions'] = Position::pluck('name', 'id')->toArray();
        }
    }

    private function resolveCatalogValue($key, $value)
    {
        $this->loadCatalogs();
        if ($key === 'campaign_id') return $this->catalogs['campaigns'][$value] ?? "ID: $value";
        if ($key === 'department_id') return $this->catalogs['departments'][$value] ?? "ID: $value";
        if ($key === 'position_id') return $this->catalogs['positions'][$value] ?? "ID: $value";
        return $value;
    }

    // --- MODAL DETALLES ---
    public function showDetails($id)
    {
        $record = Activity::with(['causer', 'subject'])->find($id);
        if (!$record) return;

        $props = $record->properties->toArray();
        $beforeRaw = $props['old'] ?? $props['before'] ?? [];
        $afterRaw  = $props['attributes'] ?? $props['after'] ?? [];

        $this->selectedLog = [
            'id' => $record->id,
            'description' => $record->description,
            'causer' => $record->causer->name ?? 'Sistema',
            'date' => $record->created_at->format('d/m/Y H:i:s'),
            'ip' => $props['ip'] ?? 'N/A',
            'before' => $this->formatAttributes($beforeRaw),
            'after'  => $this->formatAttributes($afterRaw),
        ];

        $this->dispatch('open-modal');
    }

    private function formatAttributes($attributes)
    {
        $formatted = [];
        foreach ($attributes as $key => $value) {
            if (in_array($key, self::HIDDEN_FIELDS)) continue;
            $niceKey = ucfirst(str_replace('_', ' ', $key));
            $formatted[$niceKey] = $this->resolveCatalogValue($key, $value);
        }
        return $formatted;
    }

    public function getSubjectDisplayName($log) {
        if ($log->subject) return $log->subject->name . ' ' . ($log->subject->ap_paterno ?? '');
        if (isset($log->properties['attributes']['name'])) return $log->properties['attributes']['name'] . ' (Eliminado)';
        return "Registro #{$log->subject_id}";
    }

    // --- RENDER ---
    public function render()
    {
        $query = Activity::with(['causer', 'subject'])
            ->where(function($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('subject_type', 'like', '%' . $this->search . '%')
                  ->orWhereHasMorph('causer', '*', function ($k) {
                      $k->where('name', 'like', '%' . $this->search . '%');
                  });
            })
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('id', 'desc');

        return view('livewire.admin.audit-table', [
            'logs' => $query->paginate($this->perPage)
        ]);
    }
}