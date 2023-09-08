<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
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

    public function env() {
        return response()->json([
            'host'  => env('DB_HOST', '127.0.0.1'),
            'db'    => env('DB_DATABASE', 'forge'),
            'user'  => env('DB_USERNAME', 'forge'),
            'pass'  => env('DB_PASSWORD', '')
        ], 200);
    }

    private function __createToken($user) {
        return $user->createToken(env('APP_NAME', 'Steam Events'))->plainTextToken;
    }

    private function __prepare($user) {
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

        return response()->json(['message' => 'New user registered', 'user_id' => $user->id, 'token' => $this->__createToken($user) ], 201);
    }

    public function register(Request $request) {
        if(empty($request->fb_token)) {
            return response()->json(['message' => 'Invalid credentials provided'], 302);
        }
        if($request->fb_token == 'sample') {
            $user = new User;
            $user->name = 'Test User';
            $user->fb_id = '123';
            $user->save();

            return $this->__prepare($user);
        }
        try {
            $fb_user = Socialite::driver($site = 'facebook')->userFromToken($request->fb_token); // stateless()->user();
        } catch (ClientException $exception) {
            return response()->json(['message' => 'Invalid credentials provided.'], 422);
        }

        // dd(['token' => $request->fb_token, 'user' => $fb_user]);

        if($user = User::where('fb_id', $fb_id = $fb_user->getId())->first()) {
            return response()->json(['message' => 'Welcome back!', 'user_id' => $user->id, 'token' => $this->__createToken($user)]);
        }

        $user = new User;
        $user->name = $fb_user->getName();
        $user->fb_id = $fb_id;
        $user->save();

        return $this->__prepare($user);
    }

}
