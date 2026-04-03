<?php

namespace App\Livewire\Admin\Users;

use App\Models\Area;
use App\Models\Campaign;
use App\Models\Department;
use App\Models\Position;
use App\Models\Sede;
use App\Models\User;
use App\Livewire\Admin\Users\Concerns\ValidatesUserOrganizationalFields;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('admin.layout')]
#[Title('| Crear usuario')]
class UserCreate extends Component
{
    use ValidatesUserOrganizationalFields;

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
        return view('livewire.admin.users.user-create', [
            'areas' => Area::orderBy('name')->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'positions' => Position::orderBy('name')->get(['id', 'name']),
            'campaigns' => Campaign::orderBy('name')->get(['id', 'name']),
            'sedes' => Sede::orderBy('sede')->get(['id', 'sede']),
            'roles' => Role::where('name', '<>', 'admin')->select('id', 'name', 'description')->orderBy('name')->get(),
        ]);
    }

    public function save(): void
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
        session()->flash('created_user_name', trim($validated['createName']).' '.trim($validated['createApPaterno']));
        $this->redirect(route('admin.users.index'), navigate: true);
    }

    public function cancel(): void
    {
        $this->redirect(route('admin.users.index'), navigate: true);
    }
}
