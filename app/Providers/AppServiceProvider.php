<?php

namespace App\Providers;

use App\Listeners\LogUserActivity;
use App\Livewire\Admin\Reports\TicketsReportsPage;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

        Event::listen(Login::class, [LogUserActivity::class, 'handleLogin']);
        Event::listen(Logout::class, [LogUserActivity::class, 'handleLogout']);

        // Alias retrocompatible: el dashboard de tickets se unificó en TicketsReportsPage.
        Livewire::component('admin.reports.tickets-dashboard', TicketsReportsPage::class);

        View::composer('admin.layout', function ($view) {
            $user = auth()->user();
            if (! $user instanceof User) {
                $view->with('passwordExpiryBanner', null);

                return;
            }

            $exp = $user->password_expires_at;
            if ($exp === null || $exp->isPast()) {
                $view->with('passwordExpiryBanner', null);

                return;
            }

            $warnDays = max(1, (int) config('helpdesk.password_warning_days_before', 5));
            if ($exp->isFuture() && $exp->lte(now()->addDays($warnDays))) {
                $view->with('passwordExpiryBanner', [
                    'expires_at' => $exp,
                    'profile_url' => route('profile'),
                ]);
            } else {
                $view->with('passwordExpiryBanner', null);
            }
        });
    }
}
