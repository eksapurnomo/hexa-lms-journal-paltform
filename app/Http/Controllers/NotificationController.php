<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationInstanceResource;
use App\Models\Notification;
use App\Models\NotificationInstance;
use App\Repositories\NotificationInstanceRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('items_per_page', 10);
        $pageNumber = $request->input('page_number', 1);
        $skip = ($pageNumber - 1) * $perPage;

        $user = Auth::guard('api')->user();

        if (!$user) {
            return $this->json('No notifications found', [
                'total_items' => 0,
                'notifications' => [],
            ]);
        }

        $notifications = NotificationInstanceRepository::query()->where('recipient_id', $user?->id)->skip($skip)->take($perPage)->orderBy('created_at', 'desc')->get();

        return $this->json($notifications ? 'Notifications found' : 'No notifications found', [
            'total_items' => count($notifications),
            'notifications' => NotificationInstanceResource::collection($notifications),
        ], 200);
    }

    public function markAsRead(NotificationInstance $notificationInstance)
    {

        if ($notificationInstance) {
            $notificationInstance->is_read = true;
            $notificationInstance->save();

            return $this->json('Notification marked as read', [
                'notifications' => NotificationInstanceResource::make($notificationInstance)
            ]);
        }

        return $this->json('Notification not found', ['notification' => 0, 'status' => 'error'], 404);
    }

    public function markAsReadAll()
    {
        $user = Auth::guard('api')->user();

        if ($user) {
            NotificationInstanceRepository::query()->where('recipient_id', $user?->id)->update(['is_read' => true]);

            return $this->json('All notifications marked as read', [
                'notifications' => NotificationInstanceResource::collection($user->notificationInstances),
            ]);
        }

        return $this->json('User not found', [], 422);
    }
}
