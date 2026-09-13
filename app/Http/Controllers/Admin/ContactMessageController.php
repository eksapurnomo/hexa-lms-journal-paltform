<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('items_per_page', 10);
        $pageNumber = $request->input('page_number', 1);
        $skip = ($pageNumber - 1) * $perPage;

        $contactMessages = ContactMessage::all()->skip($skip)->take($perPage)->values();

        return $this->json($contactMessages ? 'Contact messages found' : 'No contact message found', [
            'total_items' => count($contactMessages),
            'contact_messages' => ContactMessageResource::collection($contactMessages),
        ], $contactMessages ? 200 : 404);
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return $this->json('Contact message deleted successfully');
    }
}
