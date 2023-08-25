<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Subscriber;

class SubscriberController extends Controller
{
    public function add(Request $request) {

        if(empty($request->streamer_id)) {
            return response()->json(['message' => 'Streamer is required'], 400);
        }

        if(empty($user_id = User::find($request->streamer_id))) {
            return response()->json(['message' => 'Streamer Not Found'], 400);
        }

        if(empty($request->name)) {
            return response()->json(['message' => 'Your name is required'], 400);
        }

        if(empty($request->tier)) {
            return response()->json(['message' => 'Subscription Tier is required'], 400);
        }

        if(!in_array($request->tier, [1, 2, 3])) {
            return response()->json(['message' => 'Subscription Tiers are 1, 2, or 3'], 400);
        }

        $r = new Subscriber;
        $r->user_id = $request->streamer_id;
        $r->name    = $request->name;
        $r->tier    = $request->tier;
        $r->save();

        return response()->json(['message' => 'Thank you for subscribing to us!'], 201);
    }
}
