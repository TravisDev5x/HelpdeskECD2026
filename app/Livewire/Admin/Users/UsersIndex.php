<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Admin\Users\Concerns\ValidatesUserOrganizationalFields;
use App\Models\Area;
use App\Models\Campaign;
use App\Models\Department;
use App\Models\Position;
use App\Models\Sede;
use App\Models\AssetUser;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Models\Role;

#[Layout('admin.layout')]
#[Title('| Usuarios')]
class UsersIndex extends Component
{
    use ValidatesUserOrganizationalFields;
    use WithFileUploads;
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;
    public bool $onlyPending = false;
    public string $viewMode = 'active';
    public ?int $selectedUserId = null;
    public ?int $selectedDeleteUserId = null;
    public array $quickView = [];
    public bool $canSelectRole = false;
    public ?int $editingUserId = null;
    public array $selectedIds = [];
    public ?string $bulkFechaBaja = null;
    public ?string $bulkMotivoBaja = null;

    public ?string $deleteFechaBaja = null;
    public ?string $deleteMotivoBaja = null;

    public ?string $lastGeneratedPassword = null;
    public ?string $lastCreatedUserName = null;
    public $rhFile = null;

    public string $createName = '';
    public string $createApPaterno = '';
    public string $createApMaterno = '';
    public string $createUsuario = '';
    public ?string $createPhone = null;
    public ?string $createEmail = null;
    public ?int $createAreaId = null;
    public ?int $createDepartmentId = null;
    public ?int $createPositionId = null;
    public ?int $createCampaignId = null;
    public ?int $createRoleId = null;
    public array $createSedes = [];

    public string $editName = '';
    public string $editApPaterno = '';
    public string $editApMaterno = '';
    public string $editUsuario = '';
    public ?string $editPhone = null;
    public ?string $editEmail = null;
    public ?int $editAreaId = null;
    public ?int $editDepartmentId = null;
    public ?int $editPositionId = null;
    public ?int $editCampaignId = null;
    public ?int $editRoleId = null;
    public array $editSedes = [];
    public ?string $editPassword = null;
    public ?string $editPasswordConfirmation = null;

    protected $paginationTheme = 'bootstrap';

    public function mount(bool $onlyPending = false, bool $trashed = false): void
    {
        abort_unless(Auth::check() && Auth::user()->can('read users'), 403);
        $this->canSelectRole = Auth::user()->hasAnyRole(['Soporte', 'Admin']);
        if ($trashed) {
            $this->viewMode = 'trashed';
            $this->onlyPending = false;
        } else {
            $this->onlyPending = $onlyPending;
        }
    }

    public function render()
    {
        $query = User::query()
            ->with([
                'department:id,name',
                'position:id,name,area,extension',
                'campaign:id,name',
                'roles:id,name',
            ])
            ->withExists(['assetAssignments as has_checklist']);

        if ($this->viewMode === 'trashed') {
            $query->onlyTrashed();
        } else {
            $query->whereNull('users.deleted_at');
        }

        if ($this->viewMode === 'active' && $this->onlyPending) {
            $query->where('users.certification', 1)
                ->whereDoesntHave('assetAssignments');
        }

        if (trim($this->search) !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('users.usuario', 'like', $term)
                    ->orWhere('users.name', 'like', $term)
                    ->orWhere('users.ap_paterno', 'like', $term)
                    ->orWhere('users.ap_materno', 'like', $term)
                    ->orWhere('users.email', 'like', $term)
                    ->orWhereHas('department', fn ($dq) => $dq->where('name', 'like', $term))
                    ->orWhereHas('position', fn ($pq) => $pq->where('name', 'like', $term))
                    ->orWhereHas('campaign', fn ($cq) => $cq->where('name', 'like', $term))
                    ->orWhereHas('roles', fn ($rq) => $rq->where('name', 'like', $term));
            });
        }

        $users = $query
            ->orderBy('users.created_at')
            ->paginate($this->perPage);

        return view('livewire.admin.users.users-index', [
            'users' => $users,
            'activeCount' => User::whereNull('deleted_at')->count(),
            'trashedCount' => User::onlyTrashed()->count(),
            'areas' => Area::orderBy('name')->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'positions' => Position::orderBy('name')->get(['id', 'name']),
            'editDepartments' => Department::orderBy('name')->get(['id', 'name']),
            'editPositions' => Position::orderBy('name')->get(['id', 'name']),
            'campaigns' => Campaign::orderBy('name')->get(['id', 'name']),
            'sedes' => Sede::orderBy('sede')->get(['id', 'sede']),
            'roles' => Role::where('name', '<>', 'admin')->select('id', 'name', 'description')->orderBy('name')->get(),
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openQuickView(int $userId): void
    {
        $user = User::withTrashed()
            ->with(['department', 'position', 'campaign', 'roles'])
            ->findOrFail($userId);

        $nombreCompleto = trim(implode(' ', array_filter([
            $user->name,
            $user->ap_paterno,
            $user->ap_materno,
        ])));

        $this->selectedUserId = $user->id;
        $this->quickView = [
            'nombre_completo' => $nombreCompleto !== '' ? $nombreCompleto : ($user->name ?? '—'),
            'usuario' => $user->usuario,
            'email' => $user->email ?: '—',
            'phone' => $user->phone ?: '—',
            'certification' => (bool) $user->certification,
            'department' => $user->department?->name ?: '—',
            'position' => $user->position?->name ?: '—',
            'position_area' => $user->position?->area ?: '—',
            'campaign' => $user->campaign?->name ?: '—',
            'sede' => $user->sede ?: '—',
            'roles' => $user->roles->pluck('name')->values()->all(),
            'created_at' => $user->created_at?->format('d/m/Y H:i') ?: '—',
            'avatar_url' => $user->avatar
                ? asset('uploads/avatars/' . $user->avatar)
                : asset('uploads/avatars/default.png'),
            'profile_url' => route('admin.users.show', $user),
        ];

        $this->dispatch('open-user-quick-view');
    }

    public function openDeleteModal(int $userId): void
    {
        abort_unless(Auth::user()->can('delete user'), 403);
        if ($this->viewMode !== 'active') {
            return;
        }
        $this->selectedDeleteUserId = $userId;
        $this->deleteFechaBaja = null;
        $this->deleteMotivoBaja = null;
        $this->resetValidation(['deleteFechaBaja', 'deleteMotivoBaja']);
        $this->dispatch('open-user-delete-modal');
    }

    public function performDeleteUser(): void
    {
        abort_unless(Auth::user()->can('delete user'), 403);
        if ($this->viewMode !== 'active' || empty($this->selectedDeleteUserId)) {
            return;
        }

        $this->validate([
            'deleteFechaBaja' => ['required', 'date', 'before_or_equal:today'],
            'deleteMotivoBaja' => ['required', 'string', 'max:255'],
        ], [
            'deleteFechaBaja.required' => 'La fecha de baja es obligatoria.',
            'deleteFechaBaja.before_or_equal' => 'La fecha de baja no puede ser futura.',
            'deleteMotivoBaja.required' => 'El motivo de baja es obligatorio.',
        ]);

        $user = User::findOrFail((int) $this->selectedDeleteUserId);
        $deletedId = (int) $user->id;

        $user->certification = 0;
        $user->motivo_baja = $this->deleteMotivoBaja;
        $user->fecha_baja = $this->deleteFechaBaja;
        $user->save();

        try {
            $user->delete();
            AssetUser::where('user_id', $deletedId)->delete();
            session()->flash('message', 'Registro eliminado.');
        } catch (\Throwable $th) {
            session()->flash('error', 'Error al eliminar el registro.');
        }

        $this->selectedDeleteUserId = null;
        $this->deleteFechaBaja = null;
        $this->deleteMotivoBaja = null;
        $this->dispatch('close-user-delete-modal');
    }

    public function restoreUser(int $userId): void
    {
        abort_unless(Auth::user()->can('delete user'), 403);
        if ($this->viewMode !== 'trashed') {
            return;
        }

        try {
            $user = User::withTrashed()->findOrFail($userId);

            User::withoutEvents(function () use ($user) {
                $user->restore();
                $user->motivo_baja = null;
                $user->fecha_baja = null;
                $user->save();
            });

            if (method_exists($user, 'auditAction')) {
                $before = [];
                $after = $user->only(['name', 'email', 'usuario']);
                $user->auditAction('User Restored by '.Auth::user()->name, $before, $after);
            }

            session()->flash('message', 'Se restauró el usuario correctamente.');
        } catch (\Throwable $th) {
            session()->flash('error', 'Error al restaurar: '.$th->getMessage());
        }
    }

    public function setViewMode(string $mode): void
    {
        if (!in_array($mode, ['active', 'trashed'], true)) {
            return;
        }

        $this->viewMode = $mode;
        $this->onlyPending = false;
        $this->selectedIds = [];
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        abort_unless(Auth::user()->can('create user'), 403);
        $this->resetCreateForm();
        $this->dispatch('open-user-create-modal');
    }

    public function createUser(): void
    {
        abort_unless(Auth::user()->can('create user'), 403);

        $this->createEmail = trim((string) ($this->createEmail ?? ''));
        $this->createPhone = $this->nullableString($this->createPhone);

        $validated = $this->validate([
            'createName' => ['required', 'string', 'max:255'],
            'createApPaterno' => ['required', 'string', 'max:255'],
            'createApMaterno' => ['required', 'string', 'max:255'],
            'createUsuario' => ['required', 'string', 'max:255', 'unique:users,usuario'],
            'createPhone' => ['nullable', 'string', 'size:10', Rule::unique('users', 'phone')],
            'createEmail' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'createAreaId' => ['nullable', 'integer', 'exists:areas,id'],
            'createDepartmentId' => ['nullable', 'integer', 'exists:departments,id'],
            'createPositionId' => ['nullable', 'integer', 'exists:positions,id'],
            'createCampaignId' => ['nullable', 'integer', 'exists:campaigns,id'],
            'createRoleId' => [$this->canSelectRole ? 'required' : 'nullable', 'integer', 'exists:roles,id'],
            'createSedes' => ['nullable', 'array'],
            'createSedes.*' => ['integer', 'exists:sedes,id'],
        ], [
            'createRoleId.required' => 'Selecciona un rol para el usuario.',
            'createEmail.unique' => 'Ese correo electrónico ya está registrado.',
            'createPhone.unique' => 'Ese número de teléfono ya está registrado.',
        ]);

        $temporalPassword = Str::password(12);

        $user = User::create([
            'name' => trim($validated['createName']),
            'ap_paterno' => trim($validated['createApPaterno']),
            'ap_materno' => trim($validated['createApMaterno']),
            'usuario' => trim($validated['createUsuario']),
            'phone' => $validated['createPhone'],
            'email' => trim($validated['createEmail']),
            'area_id' => $validated['createAreaId'] ?? null,
            'department_id' => $validated['createDepartmentId'] ?? null,
            'position_id' => $validated['createPositionId'] ?? null,
            'campaign_id' => $validated['createCampaignId'] ?? null,
            'password' => $temporalPassword,
            'password_expires_at' => Carbon::now(),
        ]);

        $selectedSedes = collect($validated['createSedes'] ?? [])->filter()->map(fn ($id) => (int) $id)->values()->all();
        if (!empty($selectedSedes)) {
            $user->sedes()->sync($selectedSedes);
            $legacySede = Sede::whereIn('id', $selectedSedes)->orderBy('sede')->value('sede');
            $user->sede = $legacySede;
            $user->save();
        }

        if ($this->canSelectRole && !empty($validated['createRoleId'])) {
            $user->assignRole((int) $validated['createRoleId']);
        } else {
            $basicRole = Role::where('name', 'Basico')->first();
            if ($basicRole) {
                $user->assignRole($basicRole->id);
            }
        }

        $this->lastGeneratedPassword = $temporalPassword;
        $this->lastCreatedUserName = trim($validated['createName']).' '.trim($validated['createApPaterno']);
        session()->flash('message', 'Usuario creado correctamente.');
        $this->resetCreateForm();
        $this->dispatch('close-user-create-modal');
    }

    public function resetPassword(int $userId): void
    {
        abort_unless(Auth::user()?->hasAnyRole(['Admin', 'Soporte']), 403);

        $user = User::findOrFail($userId);
        $temporalPassword = Str::password(12);

        $user->update([
            'password' => $temporalPassword,
            'password_expires_at' => Carbon::now(),
        ]);

        $this->lastGeneratedPassword = $temporalPassword;
        $this->lastCreatedUserName = trim(implode(' ', array_filter([$user->name, $user->ap_paterno])));

        if ($user->email) {
            try {
                $user->notify(new \App\Notifications\PasswordResetByAdmin($temporalPassword));
            } catch (\Throwable) {
                // El correo no se pudo enviar (SMTP no disponible); la contraseña ya se restableció correctamente.
            }
        }
    }

    public function openEditModal(int $userId): void
    {
        abort_unless(Auth::user()->can('update user'), 403);
        $user = User::with(['roles:id,name', 'sedes:id,sede'])->findOrFail($userId);

        $this->resetValidation();
        $this->editingUserId = $user->id;
        $this->editName = (string) $user->name;
        $this->editApPaterno = (string) ($user->ap_paterno ?? '');
        $this->editApMaterno = (string) ($user->ap_materno ?? '');
        $this->editUsuario = (string) $user->usuario;
        $this->editPhone = $user->phone;
        $this->editEmail = $user->email;
        $this->editAreaId = $user->area_id ? (int) $user->area_id : null;
        $this->editDepartmentId = $user->department_id ? (int) $user->department_id : null;
        $this->editPositionId = $user->position_id ? (int) $user->position_id : null;
        $this->editCampaignId = $user->campaign_id ? (int) $user->campaign_id : null;
        $this->editRoleId = $user->roles->isNotEmpty() ? (int) $user->roles->first()->id : null;
        $this->editSedes = $user->sedes->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $this->editPassword = null;
        $this->editPasswordConfirmation = null;

        $this->dispatch('open-user-edit-modal');
    }

    public function updateUser(): void
    {
        abort_unless(Auth::user()->can('update user'), 403);
        abort_if(empty($this->editingUserId), 404);

        $user = User::findOrFail((int) $this->editingUserId);

        $this->editEmail = trim((string) ($this->editEmail ?? ''));
        $this->editPhone = $this->nullableString($this->editPhone);

        $validated = $this->validate([
            'editName' => ['required', 'string', 'max:255'],
            'editApPaterno' => ['required', 'string', 'max:255'],
            'editApMaterno' => ['required', 'string', 'max:255'],
            'editUsuario' => ['required', 'string', 'max:255', 'unique:users,usuario,' . $user->id],
            'editPhone' => ['nullable', 'string', 'size:10', Rule::unique('users', 'phone')->ignore($user->id)],
            'editEmail' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'editAreaId' => ['nullable', 'integer', 'exists:areas,id'],
            'editDepartmentId' => ['nullable', 'integer', 'exists:departments,id'],
            'editPositionId' => ['nullable', 'integer', 'exists:positions,id'],
            'editCampaignId' => ['nullable', 'integer', 'exists:campaigns,id'],
            'editRoleId' => [$this->canSelectRole ? 'required' : 'nullable', 'integer', 'exists:roles,id'],
            'editSedes' => ['nullable', 'array'],
            'editSedes.*' => ['integer', 'exists:sedes,id'],
            'editPassword' => ['nullable', 'string', 'min:8', 'same:editPasswordConfirmation'],
            'editPasswordConfirmation' => ['nullable', 'string', 'min:8'],
        ], [
            'editRoleId.required' => 'Selecciona un rol para el usuario.',
            'editPassword.same' => 'La confirmación de contraseña no coincide.',
            'editEmail.unique' => 'Ese correo electrónico ya está registrado.',
            'editPhone.unique' => 'Ese número de teléfono ya está registrado.',
        ]);

        $payload = [
            'name' => trim($validated['editName']),
            'ap_paterno' => trim($validated['editApPaterno']),
            'ap_materno' => trim($validated['editApMaterno']),
            'usuario' => trim($validated['editUsuario']),
            'phone' => $validated['editPhone'],
            'email' => trim($validated['editEmail']),
            'area_id' => $validated['editAreaId'] ?? null,
            'department_id' => $validated['editDepartmentId'] ?? null,
            'position_id' => $validated['editPositionId'] ?? null,
            'campaign_id' => $validated['editCampaignId'] ?? null,
        ];

        if (!empty($validated['editPassword'])) {
            abort_unless(Auth::user()?->hasAnyRole(['Admin', 'Soporte']), 403, 'Solo Administración o Soporte pueden restablecer la contraseña de otros usuarios.');
            $payload['password'] = $validated['editPassword'];
        }

        $user->update($payload);

        $selectedSedes = collect($validated['editSedes'] ?? [])->filter()->map(fn ($id) => (int) $id)->values()->all();
        $user->sedes()->sync($selectedSedes);
        $legacySede = Sede::whereIn('id', $selectedSedes)->orderBy('sede')->value('sede');
        $user->sede = $legacySede;
        $user->save();

        if ($this->canSelectRole && !empty($validated['editRoleId'])) {
            $user->syncRoles((int) $validated['editRoleId']);
        }

        session()->flash('message', 'Usuario actualizado.');
        $this->resetEditForm();
        $this->dispatch('close-user-edit-modal');
    }

    public function cancelEditUser(): void
    {
        $this->resetEditForm();
    }

    public function toggleSelectPage(array $userIds): void
    {
        $current = collect($this->selectedIds)->map(fn ($id) => (int) $id);
        $ids = collect($userIds)->map(fn ($id) => (int) $id);
        $allSelected = $ids->every(fn ($id) => $current->contains($id));

        if ($allSelected) {
            $this->selectedIds = $current->reject(fn ($id) => $ids->contains($id))->values()->all();
            return;
        }

        $this->selectedIds = $current->merge($ids)->unique()->values()->all();
    }

    public function clearSelection(): void
    {
        $this->selectedIds = [];
    }

    public function openBulkDeleteModal(): void
    {
        abort_unless(Auth::user()->can('delete user'), 403);
        if ($this->viewMode !== 'active' || empty($this->selectedIds)) {
            return;
        }
        $this->bulkFechaBaja = null;
        $this->bulkMotivoBaja = null;
        $this->dispatch('open-user-bulk-delete-modal');
    }

    public function performBulkDelete(): void
    {
        abort_unless(Auth::user()->can('delete user'), 403);
        if ($this->viewMode !== 'active') {
            return;
        }

        $this->validate([
            'bulkFechaBaja' => ['required', 'date', 'before_or_equal:today'],
            'bulkMotivoBaja' => ['required', 'string', 'max:255'],
        ], [
            'bulkFechaBaja.required' => 'La fecha de baja es obligatoria.',
            'bulkFechaBaja.before_or_equal' => 'La fecha de baja no puede ser futura.',
            'bulkMotivoBaja.required' => 'El motivo de baja es obligatorio.',
        ]);

        $ids = collect($this->selectedIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->reject(fn ($id) => $id === (int) Auth::id())
            ->values()
            ->all();

        if (empty($ids)) {
            session()->flash('error', 'No hay usuarios válidos para dar de baja.');
            return;
        }

        $users = User::whereIn('id', $ids)->get();
        $countDeleted = 0;

        foreach ($users as $user) {
            try {
                User::withoutEvents(function () use ($user) {
                    $user->fecha_baja = $this->bulkFechaBaja;
                    $user->motivo_baja = $this->bulkMotivoBaja;
                    $user->certification = 0;
                    $user->save();
                });

                User::withoutEvents(function () use ($user) {
                    $user->delete();
                });

                $countDeleted++;
            } catch (\Throwable $e) {
                continue;
            }
        }

        $this->selectedIds = [];
        $this->bulkFechaBaja = null;
        $this->bulkMotivoBaja = null;
        $this->dispatch('close-user-bulk-delete-modal');
        session()->flash('message', "Se dieron de baja {$countDeleted} usuario(s).");
    }

    public function restoreSelected(): void
    {
        abort_unless(Auth::user()->can('delete user'), 403);
        if ($this->viewMode !== 'trashed' || empty($this->selectedIds)) {
            return;
        }

        $ids = collect($this->selectedIds)->map(fn ($id) => (int) $id)->filter()->values()->all();
        $users = User::withTrashed()->whereIn('id', $ids)->get();
        $countRestored = 0;

        foreach ($users as $user) {
            try {
                User::withoutEvents(function () use ($user) {
                    $user->restore();
                    $user->motivo_baja = null;
                    $user->fecha_baja = null;
                    $user->save();
                });
                $countRestored++;
            } catch (\Throwable $e) {
                continue;
            }
        }

        $this->selectedIds = [];
        session()->flash('message', "Se restauraron {$countRestored} usuario(s).");
    }

    public function exportUsers()
    {
        abort_unless(Auth::check() && Auth::user()->can('read users'), 403);

        $rows = User::withTrashed()
            ->with(['department:id,name', 'position:id,name', 'campaign:id,name'])
            ->orderBy('id')
            ->get()
            ->map(function (User $user) {
                $status = $user->deleted_at ? 'BAJA' : ((optional($user->roles->first())->name === 'Suspendido') ? 'INACTIVO' : 'ACTIVO');
                return [
                    'id' => $user->id,
                    'numero_empleado' => $user->usuario,
                    'nombre_completo' => trim(implode(' ', array_filter([$user->name, $user->ap_paterno, $user->ap_materno]))),
                    'email' => $user->email,
                    'telefono' => $user->phone,
                    'departamento' => $user->department?->name,
                    'puesto' => $user->position?->name,
                    'campana' => $user->campaign?->name,
                    'estatus' => $status,
                    'fecha_baja' => optional($user->fecha_baja)->format('Y-m-d'),
                    'motivo_baja' => $user->motivo_baja,
                ];
            });

        $headers = [
            'ID',
            'NUMERO_EMPLEADO',
            'NOMBRE_COMPLETO',
            'EMAIL',
            'TELEFONO',
            'DEPARTAMENTO',
            'PUESTO',
            'CAMPANA',
            'ESTATUS',
            'FECHA_BAJA',
            'MOTIVO_BAJA',
        ];

        $filename = 'usuarios_helpdesk_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($headers, $rows) {
            $output = fopen('php://output', 'w');
            fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($output, $headers);
            foreach ($rows as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['numero_empleado'],
                    $row['nombre_completo'],
                    $row['email'],
                    $row['telefono'],
                    $row['departamento'],
                    $row['puesto'],
                    $row['campana'],
                    $row['estatus'],
                    $row['fecha_baja'],
                    $row['motivo_baja'],
                ]);
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importRhList(): void
    {
        abort_unless(Auth::check() && Auth::user()->can('update user'), 403);

        $this->validate([
            'rhFile' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
        ], [
            'rhFile.required' => 'Selecciona un archivo de RH.',
            'rhFile.mimes' => 'El archivo debe ser Excel o CSV.',
            'rhFile.max' => 'El archivo excede el tamaño permitido (10MB).',
        ]);

        $sheet = IOFactory::load($this->rhFile->getRealPath())->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);
        if (count($rows) < 2) {
            session()->flash('error', 'El archivo no contiene filas para importar.');
            $this->rhFile = null;
            return;
        }

        $header = array_shift($rows);
        $headerMap = [];
        foreach ($header as $index => $value) {
            $key = Str::of((string) $value)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->value();
            if ($key !== '') {
                $headerMap[$key] = $index;
            }
        }

        $statusIdx = $this->findHeaderIndex($headerMap, ['estatus', 'status', 'estado']);
        $employeeIdx = $this->findHeaderIndex($headerMap, ['id', 'numero_empleado', 'no_empleado', 'empleado', 'usuario']);
        $nameIdx = $this->findHeaderIndex($headerMap, ['nombre_completo', 'nombre', 'colaborador']);
        $fechaBajaIdx = $this->findHeaderIndex($headerMap, ['fecha_baja', 'fecha_de_baja', 'fecha']);

        if ($statusIdx === null || ($employeeIdx === null && $nameIdx === null)) {
            session()->flash('error', 'El archivo RH no incluye columnas mínimas (estatus y empleado/nombre).');
            $this->rhFile = null;
            return;
        }

        $stats = [
            'processed' => 0,
            'ignored_training' => 0,
            'not_found' => 0,
            'activated' => 0,
            'deactivated' => 0,
            'assets_pending_unassign' => 0,
        ];

        $users = User::withTrashed()->get(['id', 'usuario', 'name', 'ap_paterno', 'ap_materno', 'deleted_at', 'certification', 'fecha_baja', 'motivo_baja']);
        $usersByCode = [];
        $usersByName = [];
        foreach ($users as $u) {
            if (!empty($u->usuario)) {
                $usersByCode[(string) $u->usuario] = $u;
            }
            $normalizedName = Str::of(trim(implode(' ', array_filter([$u->name, $u->ap_paterno, $u->ap_materno]))))
                ->ascii()
                ->upper()
                ->replaceMatches('/\s+/', ' ')
                ->trim()
                ->value();
            if ($normalizedName !== '') {
                $usersByName[$normalizedName] = $u;
            }
        }

        foreach ($rows as $row) {
            $stats['processed']++;
            $statusRaw = trim((string) ($row[$statusIdx] ?? ''));
            $statusKey = Str::of($statusRaw)->ascii()->upper()->replace('Ó', 'O')->value();

            if (Str::contains($statusKey, ['CAPACITACION', 'CAPA'])) {
                $stats['ignored_training']++;
                continue;
            }

            $employeeCode = $employeeIdx !== null ? trim((string) ($row[$employeeIdx] ?? '')) : '';
            $fullName = $nameIdx !== null ? trim((string) ($row[$nameIdx] ?? '')) : '';
            $user = $this->resolveUserFromRhRow($employeeCode, $fullName, $usersByCode, $usersByName);
            if (!$user) {
                $stats['not_found']++;
                continue;
            }

            if (Str::contains($statusKey, ['BAJA', 'INACTIVO'])) {
                $bajaDate = $this->parseRhDate($fechaBajaIdx !== null ? ($row[$fechaBajaIdx] ?? null) : null) ?? now()->toDateString();
                $wasDeleted = !is_null($user->deleted_at);

                DB::transaction(function () use ($user, $bajaDate, &$stats) {
                    $user->certification = 0;
                    $user->fecha_baja = $bajaDate;
                    $user->motivo_baja = 'Baja por importación RH';
                    $user->save();
                    if (is_null($user->deleted_at)) {
                        $user->delete();
                    }

                    $affected = Product::query()
                        ->where('employee_id', $user->id)
                        ->whereNull('deleted_at')
                        ->update(['status' => 'NO_ENTREGADO']);
                    $stats['assets_pending_unassign'] += $affected;
                });

                if (!$wasDeleted) {
                    $stats['deactivated']++;
                }
                continue;
            }

            if (Str::contains($statusKey, ['ACTIVO'])) {
                DB::transaction(function () use ($user, &$stats) {
                    if (!is_null($user->deleted_at)) {
                        $user->restore();
                    }
                    $user->certification = 1;
                    $user->fecha_baja = null;
                    $user->motivo_baja = null;
                    $user->save();
                });
                $stats['activated']++;
            }
        }

        $this->rhFile = null;
        session()->flash(
            'message',
            "Importación RH completada. Procesados: {$stats['processed']}, Activos: {$stats['activated']}, Bajas: {$stats['deactivated']}, Ignorados CAPA: {$stats['ignored_training']}, No encontrados: {$stats['not_found']}, Activos marcados pendiente desasignación: {$stats['assets_pending_unassign']}."
        );
    }

    public function cancelCreateUser(): void
    {
        $this->resetCreateForm();
    }

    private function resetCreateForm(): void
    {
        $this->resetValidation();
        $this->createName = '';
        $this->createApPaterno = '';
        $this->createApMaterno = '';
        $this->createUsuario = '';
        $this->createPhone = null;
        $this->createEmail = null;
        $this->createAreaId = null;
        $this->createDepartmentId = null;
        $this->createPositionId = null;
        $this->createCampaignId = null;
        $this->createRoleId = null;
        $this->createSedes = [];
    }

    private function resetEditForm(): void
    {
        $this->resetValidation();
        $this->editingUserId = null;
        $this->editName = '';
        $this->editApPaterno = '';
        $this->editApMaterno = '';
        $this->editUsuario = '';
        $this->editPhone = null;
        $this->editEmail = null;
        $this->editAreaId = null;
        $this->editDepartmentId = null;
        $this->editPositionId = null;
        $this->editCampaignId = null;
        $this->editRoleId = null;
        $this->editSedes = [];
        $this->editPassword = null;
        $this->editPasswordConfirmation = null;
    }

    private function findHeaderIndex(array $headerMap, array $aliases): ?int
    {
        foreach ($aliases as $alias) {
            if (array_key_exists($alias, $headerMap)) {
                return $headerMap[$alias];
            }
        }
        return null;
    }

    private function resolveUserFromRhRow(string $employeeCode, string $fullName, array $usersByCode, array $usersByName): ?User
    {
        if ($employeeCode !== '') {
            if (array_key_exists($employeeCode, $usersByCode)) {
                return $usersByCode[$employeeCode];
            }
        }

        if ($fullName !== '') {
            $normalizedTarget = Str::of($fullName)->ascii()->upper()->replaceMatches('/\s+/', ' ')->trim()->value();
            if (array_key_exists($normalizedTarget, $usersByName)) {
                return $usersByName[$normalizedTarget];
            }
        }

        return null;
    }

    private function parseRhDate(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }
        $raw = trim((string) $value);
        if (is_numeric($raw)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $raw)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }
        $ts = strtotime($raw);
        return $ts === false ? null : date('Y-m-d', $ts);
    }
}

