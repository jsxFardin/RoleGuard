<?php

namespace App\Providers;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            AuditLog::create([
                'user_id' => $event->user?->id,
                'event' => 'auth.login',
                'meta' => [
                    'guard' => $event->guard,
                ],
            ]);
        });

        Event::listen(Logout::class, function (Logout $event): void {
            AuditLog::create([
                'user_id' => $event->user?->id,
                'event' => 'auth.logout',
                'meta' => [
                    'guard' => $event->guard,
                ],
            ]);
        });

        Event::listen(Failed::class, function (Failed $event): void {
            AuditLog::create([
                'user_id' => $event->user?->id,
                'event' => 'auth.failed',
                'meta' => [
                    'guard' => $event->guard,
                    'credentials' => array_key_exists('email', $event->credentials ?? [])
                        ? ['email' => $event->credentials['email']]
                        : null,
                ],
            ]);
        });
    }
}

