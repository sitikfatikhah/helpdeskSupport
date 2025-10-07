<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use App\Observers\CompanyObserver;
use App\Observers\DepartmentObserver;
use App\Observers\TicketObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;
use Filament\Notifications\Livewire\DatabaseNotifications;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DatabaseNotifications::trigger('filament.notifications.database-notifications-trigger');
        User::observe(UserObserver::class);
        Ticket::observe(TicketObserver::class);
        Company::observe(CompanyObserver::class);
        Department::observe(DepartmentObserver::class);

    }

}
