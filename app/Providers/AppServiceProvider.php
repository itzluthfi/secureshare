<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        // Register model policies  
        Gate::policy(\App\Models\Project::class, \App\Policies\ProjectPolicy::class);
        Gate::policy(\App\Models\Task::class, \App\Policies\TaskPolicy::class);
        Gate::policy(\App\Models\Document::class, \App\Policies\DocumentPolicy::class);
        Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);
        
        // Calendar milestone gates
        Gate::define('createMilestone', [\App\Policies\CalendarPolicy::class, 'createMilestone']);
        Gate::define('updateMilestone', [\App\Policies\CalendarPolicy::class, 'updateMilestone']);
        Gate::define('deleteMilestone', [\App\Policies\CalendarPolicy::class, 'deleteMilestone']);
    }
}
