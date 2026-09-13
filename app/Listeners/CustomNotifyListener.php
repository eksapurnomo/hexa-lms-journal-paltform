<?php

namespace App\Listeners;

use App\Events\CustomNotifyEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\NotificationService;

class CustomNotifyListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CustomNotifyEvent $event): void
    {
        try {
            $notificationService = new NotificationService;
            $notificationService->sends($event->tokens, $event->title, $event->message);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
