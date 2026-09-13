<?php

namespace App\Http\Controllers\WebAdmin;

use App\Events\SubscriberMailEvent;
use App\Http\Controllers\Controller;
use App\Models\NewslatterSubscription;
use Illuminate\Http\Request;

class NewslatterController extends Controller
{
    public function index()
    {
        $subscribers = NewslatterSubscription::orderBy('created_at', 'desc')->withTrashed()->paginate(20);
        return view('newslatter.index', compact('subscribers'));
    }

    public function delete($id)
    {
        $subscriber = NewslatterSubscription::findOrFail($id);
        $subscriber->delete();
        return to_route('newslatter.index')->with('success', 'Subscriber deleted successfully.');
    }
    public function restore($id)
    {
        $subscriber = NewslatterSubscription::onlyTrashed()->findOrFail($id);
        $subscriber->restore();
        return to_route('newslatter.index')->with('success', 'Subscriber restored successfully.');
    }

    public function sendMail($id)
    {
        $subscriber = NewslatterSubscription::findOrFail($id);

        $email = $subscriber->email;
        $subject = " Welcome to the Family! You’re Officially Subscribed to Our Newsletter";

        SubscriberMailEvent::dispatch($email, $subject);

        $subscriber->update(['status' => 1]);

        return to_route('newslatter.index')->with('success', 'Mail sent successfully.');
    }
}
