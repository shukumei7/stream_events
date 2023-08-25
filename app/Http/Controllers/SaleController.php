<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MerchSale;

class SaleController extends Controller
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

        if(empty($request->item_name)) {
            return response()->json(['message' => 'An Item Name is required'], 400);
        }

        if(empty($request->amount)) {
            return response()->json(['message' => 'How many do you want to buy?'], 400);
        }

        if(!is_numeric($request->amount)) {
            return response()->json(['message' => 'You have an invalid amount of items'], 400);
        }

        if($request->amount < 1) {
            return response()->json(['message' => 'You must buy 1 or more of this item'], 400);
        }

        if(empty($request->price)) {
            return response()->json(['message' => 'How much do you want to buy this?'], 400);
        }

        if(!is_numeric($request->price)) {
            return response()->json(['message' => 'You have an invalid price'], 400);
        }

        if($request->price < 1) {
            return response()->json(['message' => 'Your price must be more than or equal to $1'], 400);
        }

        $r = new MerchSale;
        $r->user_id = $request->streamer_id;
        $r->name    = $request->name;
        $r->item_name  = $request->item_name;
        $r->amount  = $request->amount;
        $r->price  = $request->price;
        $r->save();

        return response()->json(['message' => 'Thank you for your purchase of '.$r->name.'!'], 201);
    }
}
