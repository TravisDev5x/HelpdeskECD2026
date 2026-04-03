<?php

namespace App\Livewire\Admin\Users;

use App\Models\Area;
use App\Models\Asset;
use App\Models\AssetUser;
use App\Models\Campaign;
use App\Models\Department;
use App\Models\Position;
use App\Models\Sede;
use App\Models\User;
use App\Livewire\Admin\Users\Concerns\ValidatesUserOrganizationalFields;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('admin.layout')]
#[Title('| Editar usuario')]
class UserEdit extends Component
{
    use ValidatesUserOrganizationalFields;

    public User $user;

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

    /** @var array<int, bool> */
    public array $assetCheckbox = [];

    public bool $canSelectRole = false;

    public function mount(User $user): void
    {
        abort_unless(Auth::check() && Auth::user()->can('update user'), 403);
        $this->canSelectRole = Auth::user()->hasAnyRole(['Soporte', 'Admin']);

        $user->load(['roles:id,name', 'sedes:id,sede']);

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

        foreach (Asset::orderBy('name')->get(['id']) as $asset) {
            $this->assetCheckbox[$asset->id] = AssetUser::where('user_id', $user->id)->where('asset_id', $asset->id)->exists();
        }
    }

    public function render()
    {
        return view('livewire.admin.users.user-edit', [
            'areas' => Area::orderBy('name')->get(['id', 'name']),
            'editDepartments' => Department::orderBy('name')->get(['id', 'name']),
            'editPositions' => Position::orderBy('name')->get(['id', 'name']),
            'campaigns' => Campaign::orderBy('name')->get(['id', 'name']),
            'sedes' => Sede::orderBy('sede')->get(['id', 'sede']),
            'roles' => Role::where('name', '<>', 'admin')->select('id', 'name', 'description')->orderBy('name')->get(),
            'assets' => Asset::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function save(): void
    {
        abort_unless(Auth::user()->can('update user'), 403);

        $user = User::findOrFail($this->user->id);

        $this->editEmail = trim((string) ($this->editEmail ?? ''));
        $this->editPhone = $this->nullableString($this->editPhone);

        $validated = $this->validate([
            'editName' => ['required', 'string', 'max:255'],
            'editApPaterno' => ['required', 'string', 'max:255'],
            'editApMaterno' => ['required', 'string', 'max:255'],
            'editUsuario' => ['required', 'string', 'max:255', 'unique:users,usuario,'.$user->id],
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

        if (! empty($validated['editPassword'])) {
            abort_unless(Auth::user()?->hasAnyRole(['Admin', 'Soporte']), 403, 'Solo Administración o Soporte pueden restablecer la contraseña de otros usuarios.');
        }

        foreach (Asset::select('id', 'name')->orderBy('name')->get() as $value) {
            $checked = ! empty($this->assetCheckbox[$value->id]);
            if ($checked) {
                $assetExist = AssetUser::where('user_id', $user->id)->where('asset_id', $value->id)->get();
                if ($assetExist->count() == 0) {
                    $assetUser = new AssetUser();
                    $assetUser->asset_id = $value->id;
                    $assetUser->user_id = $user->id;
                    try {
                        $assetUser->save();
                    } catch (\Throwable $th) {
                    }
                }
            } else {
                AssetUser::where('user_id', $user->id)->where('asset_id', $value->id)->delete();
            }
        }

        if (! empty($validated['editPassword'])) {
            $before = [];
            $after = ['password_changed' => true];
            if (method_exists($user, 'auditAction')) {
                $user->auditAction('Password Changed', $before, $after);
            }
        }

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

        if (! empty($validated['editPassword'])) {
            $payload['password'] = $validated['editPassword'];
            $payload['password_expires_at'] = Carbon::now();
        }

        $user->update($payload);

        $selectedSedes = collect($validated['editSedes'] ?? [])->filter()->map(fn ($id) => (int) $id)->values()->all();
        $user->sedes()->sync($selectedSedes);
        $legacySede = Sede::whereIn('id', $selectedSedes)->orderBy('sede')->value('sede');
        $user->sede = $legacySede;
        $user->save();

        if ($this->canSelectRole && ! empty($validated['editRoleId'])) {
            $user->syncRoles((int) $validated['editRoleId']);
        }

        session()->flash('message', 'Usuario actualizado.');
        $this->redirect(route('admin.users.index'), navigate: true);
    }

    public function cancel(): void
    {
        $this->redirect(route('admin.users.index'), navigate: true);
    }
}
