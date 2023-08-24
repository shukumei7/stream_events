<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Follower;
use App\Models\Subscriber;
use App\Models\Donation;
use App\Models\MerchSale;

class EventTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_no_user(): void
    {
        $response = $this->get('api/events');
        $response->assertStatus(401);
        $response = $this->get('api/revenues');
        $response->assertStatus(401);
        $response = $this->get('api/followers');
        $response->assertStatus(401);
        $response = $this->get('api/sales');
        $response->assertStatus(401);
    }

    public function test_invalid_user(): void
    {
        $response = $this->get('api/events?user_id=0');
        $response->assertStatus(400);
        $response = $this->get('api/revenues?user_id=0');
        $response->assertStatus(400);
        $response = $this->get('api/followers?user_id=0');
        $response->assertStatus(400);
        $response = $this->get('api/sales?user_id=0');
        $response->assertStatus(400);
    }

    public function test_events(): void
    {
        $first_id = User::first()->value('id');
        debug('First ID: '.$first_id);
        $response = $this->getJson('api/events?user_id='.$first_id);
        $updates = $response['updates'];
        debug('Count: '.number_format($c = count($updates)));
        $this->assertLessThan(101, $c);
        $first = current($updates);
        debug('Latest Time: '.$first['created_at']);
        $last = end($updates);
        debug('Earliest Time: '.$last['created_at']);
        $response = $this->getJson('api/events?user_id='.$first_id.'&before='.$last['created_at']);
        $updates = $response['updates'];
        debug('Next Count: '.number_format($c = count($updates)));
        $this->assertLessThan(101, $c);
        $first = current($updates);
        $this->assertGreaterThan($last['created_at'],$first['created_at']);
        debug('Next Latest Time: '.$first['created_at']);
        // debug(strtotime($last['created_at']) > strtotime($first['created_at']));
        $last = end($updates);
        debug('Next Earliest Time: '.$last['created_at']);
        $response->assertStatus(200);
        $response = $this->getJson('api/revenues?user_id='.$first_id);
        debug('Revenue: '.number_format($response['revenue'], 2));
        $response->assertStatus(200);
        $response = $this->getJson('api/followers?user_id='.$first_id);
        debug('Followers: '.number_format($response['followers']));
        $response->assertStatus(200);
        $response = $this->getJson('api/sales?user_id='.$first_id);
        debug('Top Sellers');
        debug($response['sellers']);
        $response->assertStatus(200);
    }

    public function test_flags() {
        $first_id = User::first()->value('id');
        debug('First ID: '.$first_id);
        $follower_id = Follower::where(['user_id' => $first_id])->get()->value('id');
        debug('Follower ID: '.$follower_id);
    }
    
}
