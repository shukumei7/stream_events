<?php

namespace App\Http\Controllers;

define('PAGE_LIMIT', 100);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Follower;
use App\Models\Subscriber;
use App\Models\Donation;
use App\Models\MerchSale;
use App\Models\Flag;

use Carbon\Carbon;

class EventController extends Controller
{
    public function index() {
        $user = Auth::user();
        $lt = 'before';
        $at = 'after';
        $marker = false;
        if($has_at = request()->has($at)) $marker = Carbon::parse(request()->input($at));
        else if(request()->has($lt)) $marker = Carbon::parse(request()->input($lt));

        $updates = $this->__searchEvents($user, $has_at, $marker);

        usort($updates, function($a, $b) { 
            if(($at = strtotime($a['created_at'])) == ($bt = strtotime($b['created_at']))) 
                return $a['id'] < $b['id']; 
            return $at < $bt;
        });

        $updates = array_slice($updates, 0, PAGE_LIMIT);

        return response()->json([ 'updates' => array_values($updates)], 200);
    }

    private function __searchEvents($user, $has_at, $marker) {
        if(empty($marker)) {
            return array_merge(
                $user->followers()->orderBy('created_at', 'desc')->limit(PAGE_LIMIT)->get()->toArray(),
                $user->donations()->orderBy('created_at', 'desc')->limit(PAGE_LIMIT)->get()->toArray(),
                $user->subscribers()->orderBy('created_at', 'desc')->limit(PAGE_LIMIT)->get()->toArray(),
                $user->sales()->orderBy('created_at', 'desc')->limit(PAGE_LIMIT)->get()->toArray()
            );
        }
        return array_merge(
            $user->followers()->whereDate('created_at', $has_at ? '>' : '<', $marker)->orderBy('created_at', 'desc')->limit(PAGE_LIMIT)->get()->toArray(),
            $user->donations()->whereDate('created_at', $has_at ? '>' : '<', $marker)->orderBy('created_at', 'desc')->limit(PAGE_LIMIT)->get()->toArray(),
            $user->subscribers()->whereDate('created_at', $has_at ? '>' : '<', $marker)->orderBy('created_at', 'desc')->limit(PAGE_LIMIT)->get()->toArray(),
            $user->sales()->whereDate('created_at', $has_at ? '>' : '<', $marker)->orderBy('created_at', 'desc')->limit(PAGE_LIMIT)->get()->toArray()
        );
    }

    public function revenue() {
        $user = Auth::user();
        $thirty_days = date(DATE_FORMAT, strtotime('-30 days'));
        $total = 0;
        $d = $user->donations()->whereDate('created_at', '>', $thirty_days)->get()->toArray();
        foreach($d as $a) {
            $a['currency'] == 'USD' && $total += $a['amount'] * 1.3;
            $a['currency'] == 'CAD' && $total += $a['amount'];
        }
        $s = $user->subscribers()->whereDate('created_at', '>', $thirty_days)->get()->toArray();
        foreach($s as $a) {
            $total += $a['tier'] * 5;
        }
        $m = $user->sales()->whereDate('created_at', '>', $thirty_days)->get()->toArray();
        foreach($m as $a) {
            $total += $a['amount'] * $a['price'];
        }
        return response()->json(['revenue' => $total], 200);
    }

    public function followers() {
        $user = Auth::user();
        $thirty_days = date(DATE_FORMAT, strtotime('-30 days'));
        $d = $user->followers()->whereDate('created_at', '>', $thirty_days)->count();
        return response()->json(['followers' => $d]);
    }

    public function sales() {
        $user = Auth::user();
        $thirty_days = date(DATE_FORMAT, strtotime('-30 days'));
        $d = $user->sales()->whereDate('created_at', '>', $thirty_days)->get()->toArray();
        $items = [];
        foreach($d as $a) {
            !isset($items[$n = $a['item_name']]) && $items[$n] = 0;
            $items[$n] += $a['amount'] * $a['price'];
        }
        arsort($items);
        return response()->json(['items' => array_slice($items, 0, 3)]);
    }

    public function flag() {
        $user = Auth::user();
        $tb = 'table';
        $t_id = 'table_id';
        if(!request()->has($tb) || !request()->has($t_id)) {
            return response()->json(['message' => 'Invalid Input'], 400);
        }
        $tb_class = request()->input($tb);
        $tb_id = request()->input($t_id);
        if(empty(DB::table($tb_class)->where(['user_id' => $id, 'id' => $tb_id])->count())) {
            return response()->json(['message' => 'Invalid Input'], 400);
        }
        $flagged = $user->flags()->where($data = ['table_id' => $tb_id, 'table' => $tb_class ])->value('id');
        if(empty($flagged)) {
            $flag = Flag::factory()->create($data);
            return response()->json(['message' => 'Marked as read', 'flag' => 1], 201);
        }
        // debug($flagged);
        Flag::where('id', $flagged)->delete();
        return response()->json(['message' => 'Marked as unread', 'flag' => 0], 201);
    }

    
}
