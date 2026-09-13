<?php

namespace App\Http\Controllers\WebAdmin;

use App\Enum\NotificationTypeEnum;
use App\Events\CustomNotifyEvent;
use App\Events\NotifyEvent;
use App\Http\Controllers\Controller;
use App\Repositories\NotificationInstanceRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomNotificationController extends Controller
{
    public function index(Request $request)
    {

        if ($request->user_scope_filter == 'all') {
            $users = UserRepository::query()->where('is_admin', false)->get();
        } else if ($request->user_scope_filter == 'instructor') {
            $users = UserRepository::query()->where('is_admin', false)->whereHas('instructor')->get();
        } else if ($request->user_scope_filter == 'student') {
            $users = UserRepository::query()->where('is_admin', false)->whereDoesntHave('instructor')->get();
        } else {
            $users = UserRepository::query()->where('is_admin', false)->get();
        }


        return view('notification.custom.index', compact('users'));
    }

    public function sendMessage(Request $request)
    {

        if (!isset($request->confirm)) {
            return back()->withInput()->with([
                'confirmation_not_enabled' => 'Please check the confirmation box before proceeding.',
            ]);
        }

        $request->validate([
            'title' => 'required',
            'message' => 'required',
            'users' => 'required|array|min:1',
        ], [
            'users.required' => 'Selecting a user is mandatory. Please ensure at least one user is selected',
        ]);

        $type = NotificationTypeEnum::CustomNotification;
        $getNotifyId = NotificationRepository::query()->where('type', $type->value)->first();

        if (!$getNotifyId->is_enabled) {
            return back()->with([
                'notification_not_enabled' => "It seems you haven’t enabled custom notifications. Please check the box to activate this feature.",
            ]);
        }

        foreach ($request->users as $user) {

            $title = $request->title;
            $message = $request->message;

            $client = UserRepository::find($user);

            if (Str::contains($title, '{user_name}') || Str::contains($message, '{user_name}')) {
                $title = str_replace('{user_name}', $client->name,  $title);
                $message = str_replace('{user_name}', $client->name, $message);
            }

            NotificationInstanceRepository::create([
                'notification_id' => $getNotifyId->id,
                'recipient_id' => $client->id,
                'title' => $title,
                'content' => $message,
                'metadata' => json_encode($request->all()),
            ]);

            $tokens = $client->fcmDeviceTokens()->pluck('token')->toArray();

            if (!empty($tokens)) {
                CustomNotifyEvent::dispatch($tokens, $title, $message);
            }
        }

        return back()->withSuccess('Notification sent successfully');
    }
}
