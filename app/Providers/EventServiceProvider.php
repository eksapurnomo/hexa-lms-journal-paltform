<?php

namespace App\Providers;

use App\Events\CustomNotifyEvent;
use App\Events\MailSendEvent;
use App\Events\NotifyEvent;
use App\Events\SubscriberMailEvent;
use App\Listeners\CustomNotifyListener;
use App\Listeners\MailSendListener;
use App\Listeners\NotifyListener;
use App\Listeners\SubscriberMailListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Tymon\JWTAuth\Claims\Custom;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        MailSendEvent::class => [
            MailSendListener::class
        ],
        NotifyEvent::class => [
            NotifyListener::class
        ],
        CustomNotifyEvent::class => [
            CustomNotifyListener::class,
        ],
        SubscriberMailEvent::class => [
            SubscriberMailListener::class
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
