<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Follower;
use App\Models\Subscriber;
use App\Models\Donation;
use App\Models\MerchSale;
use App\Models\Flag;

class EventTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void {
        parent::setUp();
        $count = 1; // User::count() + 1;
        $response = $this->postJson('api/users', [ 'fb_id' => $count, 'fb_name' => 'Test User '.$count]);
    }

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
        $response = $this->getJson('api/events?user_id='.$first_id);

        $updates = $response['updates'];
        debug('Count: '.number_format($c = count($updates)));
        $this->assertLessThan(101, $c);

        $last = current($updates);
        $first = end($updates);
        debug('Latest Time: '.$first['time']);
        debug('Oldest Time: '.$last['time']);
        
        $response = $this->getJson('api/events?user_id='.$first_id.'&before='.$last['time']);
        $response->assertStatus(200);
        $updates = $response['updates'];
        debug('Next Count: '.number_format($c = count($updates)));
        $this->assertLessThan(101, $c);
        
        $last = current($updates);
        $this->assertGreaterThanOrEqual(strtotime($last['time']),strtotime($first['time']));

        $first = end($updates);
        debug('Next Latest Time: '.$first['time']);
        debug('Next Oldest Time: '.$last['time']);

        $random_past = date(DATE_FORMAT, strtotime('-'.rand(1,10).' days'));
        $response = $this->getJson('api/events?user_id='.$first_id.'&after='.$random_past);
        $response->assertStatus(200);

        debug('Random Past: '.$random_past);
        $updates = $response['updates'];
        $last = current($updates);
        $first = end($updates);
        debug('Count: '.number_format($c = count($updates)));
        debug('Latest Time: '.$first['time']);
        debug('Oldest Time: '.$last['time']);
        $this->assertLessThan(101, $c);
        $this->assertGreaterThanOrEqual(strtotime($random_past),strtotime($last['time']));

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
        $response = $this->postJson('api/flags', ['user_id' => $first_id, 'table' => 'followers', 'table_id' => $follower_id]);
        debug($response['message']);
        $this->assertContains($response->getStatusCode(), [201, 204]);
    }
    
}
