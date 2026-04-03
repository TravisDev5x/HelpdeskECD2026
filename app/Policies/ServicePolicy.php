<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use App\Support\Tickets\TicketQueryByRole;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServicePolicy
{
    use HandlesAuthorization;

    public function before(User $user): ?bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('read services');
    }

    public function view(User $user, Service $service): bool
    {
        if (! $user->can('read services')) {
            return false;
        }

        return TicketQueryByRole::userCanAccessService($user, $service);
    }

    public function create(User $user): bool
    {
        return $user->can('create service');
    }

    public function update(User $user, Service $service): bool
    {
        if (! $user->can('update service')) {
            return false;
        }

        return TicketQueryByRole::userCanAccessService($user, $service);
    }

    /**
     * Reasignar el ticket a otra área vía cambio de tipo de falla (mesa de ayuda).
     */
    public function escalate(User $user, Service $service): bool
    {
        if (! $user->can('escalate service')) {
            return false;
        }

        return TicketQueryByRole::userCanAccessService($user, $service);
    }

    public function delete(User $user, Service $service): bool
    {
        return false;
    }

    public function restore(User $user, Service $service): bool
    {
        return false;
    }

    public function forceDelete(User $user, Service $service): bool
    {
        return false;
    }
}
