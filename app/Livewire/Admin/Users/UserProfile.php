<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('admin.layout')]
#[Title('| Perfil')]
class UserProfile extends Component
{
    use WithFileUploads;

    public User $user;

    public $avatar;

    public string $name = '';

    public ?string $email = null;

    public ?string $password = null;

    public ?string $password_confirmation = null;

    public function mount(): void
    {
        $actor = Auth::user();
        abort_unless($actor, 403);

        $this->user = $actor;
        $this->name = (string) $actor->name;
        $this->email = $actor->email;
    }

    public function saveAvatar(): void
    {
        $this->validate([
            'avatar' => ['required', 'image', 'max:5120'],
        ], [
            'avatar.required' => 'Selecciona una imagen.',
            'avatar.image' => 'El archivo debe ser una imagen.',
        ]);

        $this->user = Auth::user();
        $filename = $this->user->usuario.'.png';
        $path = $this->avatar->getRealPath();
        Image::make($path)->resize(300, 300)->save(public_path('uploads/avatars/'.$filename));

        $this->user->avatar = $filename;
        $this->user->save();

        $this->reset('avatar');
        session()->flash('message', 'Imagen actualizada.');
    }

    public function saveProfile(): void
    {
        $this->user = Auth::user();
        $userId = $this->user->id;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                Rule::unique('users')->ignore($userId),
            ],
        ];

        if ($this->password !== null && $this->password !== '') {
            $rules['password'] = ['confirmed', Password::min(8)->mixedCase()->numbers()];
        }

        $validated = $this->validate($rules);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
        ];

        if (! empty($validated['password'] ?? null)) {
            $payload['password'] = $validated['password'];
        }

        $this->user->update($payload);

        $this->password = null;
        $this->password_confirmation = null;

        session()->flash('message', 'Usuario actualizado.');
    }

    public function render()
    {
        return view('livewire.admin.users.user-profile');
    }
}
