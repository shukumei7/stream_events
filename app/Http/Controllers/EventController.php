<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Follower;
use App\Models\Subscriber;
use App\Models\Donation;
use App\Models\MerchSale;
use App\Models\Flag;

class EventController extends Controller
{
    public function index() {
        if(!is_numeric($id = $this->_authenticate())) {
            return $id;
        }
        $lt = 'before';
        $at = 'after';
        $marker = date(DATE_FORMAT);
        if($has_at = request()->has($at)) $marker = request()->input($at);
        else if(request()->has($lt)) $marker = request()->input($lt);
        $updates = array_merge(Follower::where([ 'user_id' => $id ])->whereDate('created_at', $has_at ? '>' : '<', $marker)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->limit(PAGE_SIZE)->get()->toArray(),
            Donation::where([ 'user_id' => $id ])->whereDate('created_at', $has_at ? '>' : '<', $marker)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->limit(PAGE_SIZE)->get()->toArray(),
            Subscriber::where([ 'user_id' => $id ])->whereDate('created_at', $has_at ? '>' : '<', $marker)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->limit(PAGE_SIZE)->get()->toArray(),
            MerchSale::where([ 'user_id' => $id ])->whereDate('created_at', $has_at ? '>' : '<', $marker)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->limit(PAGE_SIZE)->get()->toArray());

        usort($updates, function($a, $b) { 
            if(($at = strtotime($a['created_at'])) == ($bt = strtotime($b['created_at']))) 
                return $a['id'] > $b['id']; 
            return $at > $bt;
        });

        $updates = array_slice($updates, 0, 100);
        $output = [];
        foreach($updates as $update) {
            $message = $table = '';
            if(isset($update['item_name'])) { // merch sale
                $message = $update['name'].' bought '.number_format($update['amount']).' '.$update['item_name'].' for $'.number_format($update['price'], 2);
                $table = 'merch_sales';
            } else if(isset($update['currency'])) { // donations
                $message = $update['name'].' donated '.$update['currency'].'$'.number_format($update['amount']);
                $table = 'donations';
            } else if(isset($update['tier'])) { // subscription
                $message = $update['name'].' subscribed for Tier '.$update['tier'];
                $table = 'subscriptions';
            } else { // follower
                $message = $update['name'].' started following you';
                $table = 'followers';
            }
            $read = Flag::where([
                'user_id'   => $id, 
                'table'     => $table,
                'table_id'  => $update['id']
            ])->count();
            $output []= ['id' => $update['id'], 'time' => $update['created_at']] + compact('message', 'table', 'read');
        }

        return response()->json([ 'updates' => $output], 200);
    }

    public function revenue() {
        if(!is_numeric($id = $this->_authenticate())) {
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
        if(!is_numeric($id = $this->_authenticate())) {
            return $id;
        }
        $thirty_days = date(DATE_FORMAT, strtotime('-30 days'));
        $d = Follower::where([ 'user_id' => $id ])->whereDate('created_at', '>', $thirty_days)->count();
        return response()->json(['followers' => $d]);
    }

    public function sales() {
        if(!is_numeric($id = $this->_authenticate())) {
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
        if(!is_numeric($id = $this->_authenticate())) {
            return $id;
        }
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
        $flagged = Flag::where($data = ['user_id' => $id, 'table_id' => $tb_id, 'table' => $tb_class ])->value('id');
        if(empty($flagged)) {
            $flag = Flag::factory()->create($data);
            return response()->json(['message' => 'Marked as read'], 201);
        }
        // debug($flagged);
        Flag::where('id', $flagged)->delete();
        return response()->json(['message' => 'Marked as unread'], 201);
    }

    
}
