<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider {
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot() {
        \App\Models\Attachment::observe(\App\Observers\AttachmentObserver::class);
        \App\Models\Attendance::observe(\App\Observers\AttendanceObserver::class);
        \App\Models\Communication::observe(\App\Observers\CommunicationObserver::class);
        \App\Models\Staff::observe(\App\Observers\StaffObserver::class);
        \App\Models\Paysheet::observe(\App\Observers\PaysheetObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents() {
        return false;
    }
}
