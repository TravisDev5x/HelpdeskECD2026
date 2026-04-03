<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('admin.layout')]
#[Title('| Detalle de usuario')]
class UserShow extends Component
{
    public User $user;

    public function mount(User $user): void
    {
        abort_unless(Auth::check() && Auth::user()->can('read users'), 403);
        $this->user->load(['department', 'position', 'campaign', 'area', 'roles', 'sedes']);
    }

    public function render()
    {
        return view('livewire.admin.users.user-show');
    }
}
