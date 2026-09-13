<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotificationService
{
    private $firebaseMessaging;
    private $credentialPath = 'firebase_credentials.json';

    /**
     * Construct the NotificationServices object.
     *
     * Instantiates the Firebase Messaging Factory and provides the path to the
     * service account credentials.
     *
     * The service account credentials are expected to be in a JSON file located
     * at `public/firebase_credentials.json`.
     */
    public function __construct()
    {
        $this->firebaseMessaging = (new Factory)
            ->withServiceAccount(public_path($this->credentialPath))
            ->createMessaging();
    }

    /**
     * Send a single notification.
     *
     * @param string $deviceToken device token from where notification will be sent.
     * @param string $title title of the notification.
     * @param string $message message of the notification.
     * @return void
     */
    public function send(string $deviceToken, string $title, string $message): void
    {
        $message = CloudMessage::new()
            ->withNotification(Notification::create($title, $message));

        $this->firebaseMessaging->send($message->withChangedTarget('token', $deviceToken));
    }

    /**
     * Send multiple notifications.
     *
     * @param array $deviceTokens device tokens from where notifications will be sent.
     * @param string $title title of the notification.
     * @param string $message message of the notification.
     * @return void
     */
    public function sends(array $deviceTokens, string $title, string $message, int $courseId  = null, string $type =  null): void
    {
        $data = [
            'title' => $title,
            'message' => $message,
            'courseId' => $courseId,
            'type' => $type,
        ];

        $message = CloudMessage::new()
            ->withData($data)
            ->withNotification(Notification::create($title, $message, $courseId, $type));

        $this->firebaseMessaging->sendMulticast($message, $deviceTokens);
    }
}
