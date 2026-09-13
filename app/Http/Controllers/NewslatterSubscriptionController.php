<?php

namespace App\Http\Controllers;

use App\Models\NewslatterSubscription;
use Illuminate\Http\Request;
use Svg\Tag\Rect;

class NewslatterSubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:newslatter_subscriptions,email',
        ]);


        $subscription = new NewslatterSubscription();
        $subscription->email = $request->email;
        $subscription->status = false;
        $subscription->save();

        return response()->json(['message' => 'Subscribed successfully!'], 201);
    }
}
