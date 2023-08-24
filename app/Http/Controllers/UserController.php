<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Follower;
use App\Models\Subscriber;
use App\Models\Donation;
use App\Models\MerchSale;

class UserController extends Controller
{
    public function register(Request $request) {
        if($id = User::where(['fb_id' => $request->fb_id])->value('id')) {
            return response()->json([
                'message'   => 'User Signed In',
                'user_id'   => $id
            ], 200);    
        }
        $user = new User;
        $user->name = $request->fb_name;
        $user->fb_id = $request->fb_id;
        $user->save();
        
        Follower::factory()->count(rand(300,500))->create([ 'user_id' => $user->id ]);
        Subscriber::factory()->count(rand(300,500))->create([ 'user_id' => $user->id ]);
        Donation::factory()->count(rand(300,500))->create([ 'user_id' => $user->id ]);
        MerchSale::factory()->count(rand(300,500))->create([ 'user_id' => $user->id ]);

        return response()->json([
            'message'   => 'User Registered',
            'user_id'   => $user->id
        ], 201);
    }
}
