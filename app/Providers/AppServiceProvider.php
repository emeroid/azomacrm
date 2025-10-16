<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Enums\Role;
use App\Models\FormTemplate;
use App\Models\Order;
use App\Models\Product;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Models\OrderCommunication;
use App\Policies\OrderCommunicationPolicy;
use App\Models\User;
use App\Policies\FormTemplatePolicy;
use App\Models\FormSubmission;
use App\Observers\OrderObserver;
use App\Policies\FormSubmissionPolicy;
use App\Policies\UserPolicy;
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
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Order::class => OrderPolicy::class,
        FormTemplate::class => FormTemplatePolicy::class,
        Product::class => ProductPolicy::class,
        User::class => UserPolicy::class,
        OrderCommunication::class => OrderCommunicationPolicy::class,
        FormSubmission::class => FormSubmissionPolicy::class,
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3); 

        // General access gates
        Gate::define('access-filament', function ($user) {
            return in_array($user->role, [
                Role::ADMIN->value,
                Role::MARKETER->value, 
                Role::DELIVERY_AGENT->value,
                Role::CALL_AGENT->value,
                Role::MANAGER->value
            ]);
        });
        
        // Admin specific gates
        Gate::define('manage-users', fn($user) => $user->role === Role::ADMIN->value);
        Gate::define('manage-system', fn($user) => $user->role === Role::ADMIN->value);
        
        // Order gates
        Gate::define('view-any-order', function ($user) {
            return in_array($user->role, [
                Role::ADMIN->value,
                Role::MARKETER->value,
                Role::DELIVERY_AGENT->value,
                Role::CALL_AGENT->value,
                Role::MANAGER->value
            ]);
        });
        
        Gate::define('create-order', function ($user) {
            return in_array($user->role, [
                Role::ADMIN->value,
                Role::MARKETER->value
            ]);
        });
        
        Gate::define('update-order-status', function ($user) {
            return in_array($user->role, [
                Role::ADMIN->value,
                Role::DELIVERY_AGENT->value,
                Role::CALL_AGENT->value
            ]);
        });
        
        // Product gates
        Gate::define('view-product', function($user) {
            return in_array($user->role, [
                Role::ADMIN->value,
                Role::MARKETER->value
            ]);
        });
        
        // All roles can view
        Gate::define('manage-product', fn($user) => $user->role === Role::ADMIN->value);
        
        // Embeddable Form gates
        Gate::define('view-any-form', fn($user) => $user->role === Role::ADMIN->value);
        Gate::define('manage-own-forms', function ($user) {
            return in_array($user->role, [
                Role::ADMIN->value,
                Role::MARKETER->value
            ]);
        });
        
        // Dashboard gates
        Gate::define('view-admin-dashboard', fn($user) => $user->role === Role::ADMIN->value);
        Gate::define('view-own-dashboard', fn($user) => true); // All roles can view their own

        Gate::define('view_operation_manager_dashboard', fn($user) => 
            in_array($user->role, [
                Role::ADMIN->value,
                Role::MANAGER->value
            ])
        );

        Gate::define('approve_fund_requests', function (User $user) {
            return in_array($user->role, [
                Role::ADMIN->value,
                Role::MANAGER->value
            ]);
        });

        Gate::define('view_all_fund_requests', function (User $user) {
            return in_array($user->role, [
                Role::ADMIN->value,
                Role::MANAGER->value
            ]);
        });

        Order::observe(OrderObserver::class);

    }
}
