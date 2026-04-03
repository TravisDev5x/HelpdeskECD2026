<?php

namespace App\Livewire\Admin\Users;

use App\Models\Area;
use App\Models\Campaign;
use App\Models\Department;
use App\Models\Position;
use App\Models\Sede;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('admin.layout')]
#[Title('| Crear usuario')]
class UserCreate extends Component
{
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

    public bool $canSelectRole = false;

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->can('create user'), 403);
        $this->canSelectRole = Auth::user()->hasAnyRole(['Soporte', 'Admin']);
    }

    public function render()
    {
        $areas = Area::orderBy('name')->get(['id', 'name']);
        $allDepartments = Department::orderBy('name')->get(['id', 'name', 'area_id']);
        $allPositions = Position::orderBy('name')->get(['id', 'name', 'department_id']);

        $departments = $this->filterCascade($allDepartments, 'area_id', $this->createAreaId);
        $positions = $this->filterCascade($allPositions, 'department_id', $this->createDepartmentId);

        return view('livewire.admin.users.user-create', [
            'areas' => $areas,
            'departments' => $departments,
            'positions' => $positions,
            'campaigns' => Campaign::orderBy('name')->get(['id', 'name']),
            'sedes' => Sede::orderBy('sede')->get(['id', 'sede']),
            'roles' => Role::where('name', '<>', 'admin')->select('id', 'name', 'description')->orderBy('name')->get(),
        ]);
    }

    public function updatedCreateAreaId($value): void
    {
        $this->createDepartmentId = null;
        $this->createPositionId = null;

        if (empty($value)) {
            return;
        }

        $firstDepartment = Department::where('area_id', (int) $value)->orderBy('name')->value('id');
        $this->createDepartmentId = $firstDepartment ? (int) $firstDepartment : null;

        if ($this->createDepartmentId) {
            $this->updatedCreateDepartmentId($this->createDepartmentId);
        }
    }

    public function updatedCreateDepartmentId($value): void
    {
        $this->createPositionId = null;

        if (empty($value)) {
            return;
        }

        $firstPosition = Position::where('department_id', (int) $value)->orderBy('name')->value('id');
        $this->createPositionId = $firstPosition ? (int) $firstPosition : null;
    }

    public function save(): void
    {
        abort_unless(Auth::user()->can('create user'), 403);

        $validated = $this->validate([
            'createName' => ['required', 'string', 'max:255'],
            'createApPaterno' => ['required', 'string', 'max:255'],
            'createApMaterno' => ['required', 'string', 'max:255'],
            'createUsuario' => ['required', 'string', 'max:255', 'unique:users,usuario'],
            'createPhone' => ['nullable', 'string', 'size:10'],
            'createEmail' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'createAreaId' => ['required', 'integer', 'exists:areas,id'],
            'createDepartmentId' => ['required', 'integer', 'exists:departments,id'],
            'createPositionId' => ['required', 'integer', 'exists:positions,id'],
            'createCampaignId' => ['nullable', 'integer', 'exists:campaigns,id'],
            'createRoleId' => [$this->canSelectRole ? 'required' : 'nullable', 'integer', 'exists:roles,id'],
            'createSedes' => ['nullable', 'array'],
            'createSedes.*' => ['integer', 'exists:sedes,id'],
        ], [
            'createRoleId.required' => 'Selecciona un rol para el usuario.',
        ]);

        $department = Department::findOrFail((int) $validated['createDepartmentId']);
        $position = Position::findOrFail((int) $validated['createPositionId']);

        if ((int) $position->department_id !== (int) $department->id) {
            $this->addError('createPositionId', 'El puesto seleccionado no pertenece al departamento elegido.');

            return;
        }

        if ($department->area_id !== null && (int) $department->area_id !== (int) $validated['createAreaId']) {
            $this->addError('createDepartmentId', 'El departamento seleccionado no pertenece al área elegida.');

            return;
        }

        $temporalPassword = Str::password(12);

        $user = User::create([
            'name' => trim($validated['createName']),
            'ap_paterno' => trim($validated['createApPaterno']),
            'ap_materno' => trim($validated['createApMaterno']),
            'usuario' => trim($validated['createUsuario']),
            'phone' => $validated['createPhone'],
            'email' => $validated['createEmail'],
            'area_id' => $validated['createAreaId'],
            'department_id' => $validated['createDepartmentId'],
            'position_id' => $validated['createPositionId'],
            'campaign_id' => $validated['createCampaignId'] ?? null,
            'password' => $temporalPassword,
            'password_expires_at' => Carbon::now(),
        ]);

        $selectedSedes = collect($validated['createSedes'] ?? [])->filter()->map(fn ($id) => (int) $id)->values()->all();
        if (! empty($selectedSedes)) {
            $user->sedes()->sync($selectedSedes);
            $legacySede = Sede::whereIn('id', $selectedSedes)->orderBy('sede')->value('sede');
            $user->sede = $legacySede;
            $user->save();
        }

        if ($this->canSelectRole && ! empty($validated['createRoleId'])) {
            $user->assignRole((int) $validated['createRoleId']);
        } else {
            $basicRole = Role::where('name', 'Basico')->first();
            if ($basicRole) {
                $user->assignRole($basicRole->id);
            }
        }

        session()->flash('message', 'Usuario creado correctamente.');
        session()->flash('generated_password', $temporalPassword);
        session()->flash('created_user_name', trim($validated['createName']) . ' ' . trim($validated['createApPaterno']));
        $this->redirect(route('admin.users.index'), navigate: true);
    }

    public function cancel(): void
    {
        $this->redirect(route('admin.users.index'), navigate: true);
    }

    private function filterCascade($collection, string $foreignKey, $parentId)
    {
        if ($parentId === null) {
            return $collection->values();
        }

        $filtered = $collection->filter(
            fn ($item) => (int) $item->{$foreignKey} === (int) $parentId
        )->values();

        return $filtered->isNotEmpty() ? $filtered : $collection->values();
    }
}
