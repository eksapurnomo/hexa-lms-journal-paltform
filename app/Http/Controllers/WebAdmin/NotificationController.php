<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationUpdateRequest;
use App\Models\Notification;
use App\Models\NotificationInstance;
use App\Repositories\NotificationRepository;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = NotificationRepository::query()->latest('id')->get();
        return view('notification.index', [
            'notifications' => $notifications,
        ]);
    }

    public function edit(Notification $notification)
    {
        return view('notification.edit', [
            'notification' => $notification,
        ]);
    }

    public function switchStatus(Notification $notification)
    {
        $notification->is_enabled = !$notification->is_enabled;
        $notification->save();

        return to_route('notification.index')->withSuccess('Notification status updated');
    }

    public function update(NotificationUpdateRequest $request, Notification $notification)
    {
        NotificationRepository::updateByRequest($request, $notification);

        return to_route('notification.index')->withSuccess('Notification updated');
    }

    public function markAsRead(NotificationInstance $notificationInstance)
    {


        $notificationInstance->is_read = true;
        $notificationInstance->save();

        if ($notificationInstance->url == null) {
            return back();
        }
        return redirect($notificationInstance->url);
    }

    public function markAsReadAll()
    {
        NotificationInstance::where('recipient_id', null)->update(['is_read' => true]);
        return back()->withSuccess('All notifications marked as read');
    }
}
