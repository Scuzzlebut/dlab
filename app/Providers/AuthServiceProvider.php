<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider {
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\Attachment' => 'App\Policies\AttachmentPolicy',
        'App\Models\Attendance' => 'App\Policies\AttendancePolicy',
        'App\Models\Communication' => 'App\Policies\CommunicationPolicy',
        'App\Models\Paysheet' => 'App\Policies\PaysheetPolicy',
        'App\Models\Staff' => 'App\Policies\StaffPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot() {
        $this->registerPolicies();
    }
}
