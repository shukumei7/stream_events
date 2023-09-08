<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
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

    public function view() {
        $user = Auth::user();
        return response()->json(['message' => 'You are logged in']);
    }

    public function register(Request $request) {
        if(empty($request->fb_id)) {
            return response()->json(['message' => 'No ID specified'], 400);
        }
        if(!empty($login = $this->__login($request->fb_id, $request->fb_token))) {
            return $login;            
        }
        if(empty($request->fb_name)) {
            return response()->json(['message' => 'No name specified'], 400);
        }

        $user = new User;
        $user->name = $request->fb_name;
        $user->fb_id = $request->fb_id;
        $user->fb_token = $request->fb_token;
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

        return $this->__login($user->fb_id, $user->fb_token);
    }

    public function env() {
        return response()->json([
            'host'  => env('DB_HOST', '127.0.0.1'),
            'db'    => env('DB_DATABASE', 'forge'),
            'user'  => env('DB_USERNAME', 'forge'),
            'pass'  => env('DB_PASSWORD', '')
        ], 200);
    }

    private function __login($fb_id, $fb_token) {
        if(empty($user = User::where('fb_id', $fb_id)->where('fb_token', $fb_token)->first())) {
            return false;
        }
        Auth::login($user, true);
        return response()->json([
            'message'   => 'User Signed In',
            'user_id'   => $user->id,
            'token'     => $user->createToken(env('APP_NAME', 'Steam Events'))->plainTextToken,
        ], 200);
    }
}
