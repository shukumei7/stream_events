<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Donation;

class DonationController extends Controller
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

        if(empty($request->currency)) {
            return response()->json(['message' => 'Currency is required'], 400);
        }

        if(!in_array($request->currency, ['CAD', 'USD'])) {
            return response()->json(['message' => 'We only accept CAD or USD'], 400);
        }

        if(empty($request->amount)) {
            return response()->json(['message' => 'An amount is required'], 400);
        }

        if(!is_numeric($request->amount) || $request->amount < 1) {
            return response()->json(['message' => 'Your amount must be more than or equal to $1'], 400);
        }

        $r = new Donation;
        $r->user_id = $request->streamer_id;
        $r->name    = $request->name;
        $r->currency= $request->currency;
        $r->amount  = $request->amount;
        $r->save();

        return response()->json(['message' => 'Thank you for your donation!'], 201);
    }
}
