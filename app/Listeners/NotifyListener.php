<?php

namespace App\Listeners;

use App\Events\NotifyEvent;
use App\Repositories\CourseRepository;
use App\Repositories\NotificationInstanceRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\UserRepository;
use App\Services\NotificationService;

class NotifyListener
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
    public function handle(NotifyEvent $event): void
    {

        $notification = NotificationRepository::query()->where('type', $event?->type)->first();

        $course = CourseRepository::find(
            $event->metadata['course']['id']
        );

        // Send notification to all enrolled users
        foreach ($course?->enrollments as $enrollment) {
            $notificationContent = str_replace('{course_title}', $event?->metadata['course_name'] ?? $event->metadata['course']['title'], $notification?->content);
            $notificationContent = str_replace('{user_name}', $enrollment?->user?->name, $notificationContent);

            // Create notification instance
            NotificationInstanceRepository::create([
                'notification_id' => $notification?->id,
                'recipient_id' => $enrollment?->user?->id,
                'course_id' => $event?->currentCourseId,
                'metadata' => json_encode($event?->metadata),
                'heading' => $notification?->heading,
                'content' => $notificationContent,
            ]);

            $tokens = $enrollment?->user?->fcmDeviceTokens()->pluck('token')->toArray();

            if (!empty($tokens)) {
                try {
                    $notificationService = new NotificationService;
                    $notificationService->sends($tokens, $notification?->heading, $notificationContent, $event?->currentCourseId, $event?->type);
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
        }
    }
}
