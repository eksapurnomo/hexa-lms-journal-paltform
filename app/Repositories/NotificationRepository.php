<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Http\Requests\NotificationUpdateRequest;
use App\Models\Notification;

class NotificationRepository extends Repository
{
    public static function model()
    {
        return Notification::class;
    }

    public static function updateByRequest(NotificationUpdateRequest $request, Notification $notification)
    {
        if (isset($request->is_enabled)) {
            $isEnabled = $request->is_enabled == 'on' ? true : false;
        } else {
            $isEnabled = false;
        }

        return self::update($notification, [
            'content' => $request->content ?? $notification->content,
            'is_enabled' => $isEnabled,
        ]);
    }
}
