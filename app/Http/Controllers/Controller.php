<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\User;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function _authenticate() {
        $u_id = 'user_id';
        if(!request()->has($u_id)) {
            return response()->json([ 'message' => 'You are not logged in' ], 401);
        }
        if(empty(User::where(['id' => $id = request()->input($u_id) ])->count())) {
            return response()->json(['message' => 'Invalid User ID' ], 400);
        }
        return $id;
    }
}
