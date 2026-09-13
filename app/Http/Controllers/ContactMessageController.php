<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactMessageRequest;
use App\Repositories\ContactMessageRepository;

class ContactMessageController extends Controller
{
    public function submit(ContactMessageRequest $request)
    {
        ContactMessageRepository::create([
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        return $this->json('Message received', null, 201);
    }
}
