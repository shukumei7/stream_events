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

    public function index() {
        $users = User::get();

        debug($users);

        exit;
    }

    public function register(Request $request) {
        if(empty($request->fb_id)) {
            return response()->json(['message' => 'No ID specified'], 400);
        }
        if($id = User::where(['fb_id' => $request->fb_id])->value('id')) {
            return $this->__login($id);            
        }
        if(empty($request->fb_name)) {
            return response()->json(['message' => 'No name specified'], 400);
        }

        $user = new User;
        $user->name = $request->fb_name;
        $user->fb_id = $request->fb_id;
        $user->save();
        
        Follower::factory()->count(rand(300,500))->create([
            'user_id' => $user->id
        ]);
        Subscriber::factory()->count(rand(300,500))->create([ 
            'user_id' => $user->id
        ]);
        Donation::factory()->count(rand(300,500))->create([
            'user_id' => $user->id
        ]);
        MerchSale::factory()->count(rand(300,500))->create([
            'user_id' => $user->id
        ]);

        return response()->json([
            'message'   => 'User Registered',
            'user_id'   => $user->id
        ], 201);
    }

    public function env() {
        return response()->json([
            'host'  => env('DB_HOST', '127.0.0.1'),
            'db'    => env('DB_DATABASE', 'forge'),
            'user'  => env('DB_USERNAME', 'forge'),
            'pass'  => env('DB_PASSWORD', '')
        ], 200);
    }

    private function __login($id) {
        return response()->json([
            'message'   => 'User Signed In',
            'user_id'   => $id
        ], 200);    
    }
}
