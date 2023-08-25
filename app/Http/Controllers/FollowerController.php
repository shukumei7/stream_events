<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Follower;

class FollowerController extends Controller
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

        if(Follower::where('name', $request->name)->get()->value('id')) {
            return response()->json(['message' => 'You are already a follower'], 200);
        }

        $r = new Follower;
        $r->user_id = $request->streamer_id;
        $r->name    = $request->name;
        $r->save();

        return response()->json(['message' => 'Thank you for following us!'], 201);
    }
}
