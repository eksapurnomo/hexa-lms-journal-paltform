<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $allcontacts = ContactMessage::orderBy('id', 'DESC')->paginate(12);
        return view('contact.index', compact('allcontacts'));
    }

    public function show(ContactMessage $contact)
    {
        $contact->update(['state' => true]);

        return response()->json($contact);
    }
    public function delete(ContactMessage $contact)
    {
        $contact->delete();

        return redirect()->route('contact.index')->withSuccess('Contact message deleted successfully');
    }
}
