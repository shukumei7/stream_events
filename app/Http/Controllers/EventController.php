<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Follower;
use App\Models\Subscriber;
use App\Models\Donation;
use App\Models\MerchSale;
use App\Models\Flag;

class EventController extends Controller
{
    public function index() {
        if(!is_numeric($id = $this->__authenticate())) {
            return $id;
        }
        $lt = 'before';
        $last_query = request()->has($lt) ? request()->input($lt) : date(DATE_FORMAT);
        $updates = array_merge(Follower::where([ 'user_id' => $id ])->whereDate('created_at', '<', $last_query)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->limit(PAGE_SIZE)->get()->toArray(),
            Donation::where([ 'user_id' => $id ])->whereDate('created_at', '<', $last_query)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->limit(PAGE_SIZE)->get()->toArray(),
            Subscriber::where([ 'user_id' => $id ])->whereDate('created_at', '<', $last_query)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->limit(PAGE_SIZE)->get()->toArray(),
            MerchSale::where([ 'user_id' => $id ])->whereDate('created_at', '<', $last_query)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->limit(PAGE_SIZE)->get()->toArray());

        usort($updates, function($a, $b) { if($a['created_at'] == $b['created_at']) return $a['id'] < $b['id']; return $a['created_at'] < $b['created_at']; });
        return response()->json([ 'updates' => array_slice($updates, 0, 100)], 200);
    }

    public function revenue() {
        if(!is_numeric($id = $this->__authenticate())) {
            return $id;
        }
        $thirty_days = date(DATE_FORMAT, strtotime('-30 days'));
        $total = 0;
        $d = Donation::where([ 'user_id' => $id ])->whereDate('created_at', '>', $thirty_days)->get()->toArray();
        foreach($d as $a) {
            $a['currency'] == 'USD' && $total += $a['amount'] * 1.3;
            $a['currency'] == 'CAD' && $total += $a['amount'];
        }
        $s = Subscriber::where([ 'user_id' => $id ])->whereDate('created_at', '>', $thirty_days)->get()->toArray();
        foreach($s as $a) {
            $total += $a['tier'] * 5;
        }
        $m = MerchSale::where([ 'user_id' => $id ])->whereDate('created_at', '>', $thirty_days)->get()->toArray();
        foreach($m as $a) {
            $total += $a['amount'] * $a['price'];
        }
        return response()->json(['revenue' => $total], 200);
    }

    public function followers() {
        if(!is_numeric($id = $this->__authenticate())) {
            return $id;
        }
        $thirty_days = date(DATE_FORMAT, strtotime('-30 days'));
        $d = Follower::where([ 'user_id' => $id ])->whereDate('created_at', '>', $thirty_days)->count();
        return response()->json(['followers' => $d]);
    }

    public function sales() {
        if(!is_numeric($id = $this->__authenticate())) {
            return $id;
        }
        $thirty_days = date(DATE_FORMAT, strtotime('-30 days'));
        $d = MerchSale::where([ 'user_id' => $id ])->whereDate('created_at', '>', $thirty_days)->get()->toArray();
        $items = [];
        foreach($d as $a) {
            !isset($items[$n = $a['item_name']]) && $items[$n] = 0;
            $items[$n] += $a['amount'] * $a['price'];
        }
        arsort($items);
        return response()->json(['sellers' => array_slice($items, 0, 3)]);
    }

    public function flag() {
        if(!is_numeric($id = $this->__authenticate())) {
            return $id;
        }
        $tb = 'table';
        $t_id = 'id';
        if(!request()->has($tb) || !request()->has($t_id)) {
            return response()->json(['message' => 'Invalid Input'], 400);
        }
        $tb_class = request()->input($tb);
        $tb_id = request()->input($t_id);
        if(empty($tb_class::where(['user_id' => $id, 'id' => $tb_id])->count())) {
            return response()->json(['message' => 'Invalid Input'], 400);
        }
        $flagged = Flag::where($data = ['user_id' => $id, 'table_id' => $tb_id, 'table' => $tb_class ])->value('id');
        if(empty($flagged)) {
            $flag = Flag::factory()->create($data);
            return response()->json(['message' => 'Marked as read'], 201);
        }
        debug($flagged);
        // Flag::where('id', $flagged)->delete();
        return response()->json(['message' => 'Marked as unread'], 204);
    }

    private function __authenticate() {
        $u_id = 'user_id';
        if(!request()->has($u_id)) {
            return response()->json([ 'message' => 'You are not logged in' ], 401);
        }
        if(empty(User::where(['id' => $id = request()->input($u_id) ])->count())) {
            return response()->json([ 'message' => 'Invalid User ID' ], 400);
        }
        return $id;
    }
}
