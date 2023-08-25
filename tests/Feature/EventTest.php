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

        $lf = Follower::where('user_id', $first_id)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->first()->value('created_at');
        $ld = Donation::where('user_id', $first_id)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->first()->value('created_at');
        $ls = Subscriber::where('user_id', $first_id)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->first()->value('created_at');
        $lm = MerchSale::where('user_id', $first_id)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->first()->value('created_at');

        $latest_date = max(strtotime($lf), strtotime($ld), strtotime($ls), strtotime($lm));

        $updates = $response['updates'];
        $this->assertLessThan(101, count($updates));
        $this->assertEquals(date(DATE_FORMAT, $latest_date), date(DATE_FORMAT, strtotime(current($updates)['time'])));
        $previous = null;
        foreach($updates as $update) {
            if(!$previous) {
                $previous = $update;
                continue;
            }
            $this->assertLessThanOrEqual(strtotime($previous['time']), strtotime($update['time']));
            $previous = $update;
        }

        $response = $this->getJson('api/events?user_id='.$first_id.'&before='.$previous['time']);
        $response->assertStatus(200);
        $updates = $response['updates'];
        $this->assertLessThan(101, count($updates));
        
        foreach($updates as $update) {
            $this->assertLessThanOrEqual(strtotime($previous['time']), strtotime($update['time']));
            $previous = $update;
        }

        $random_past = date(DATE_FORMAT, strtotime('-'.rand(1,10).' days'));
        $response = $this->getJson('api/events?user_id='.$first_id.'&after='.$random_past);
        $response->assertStatus(200);

        debug('Random Past: '.$random_past);
        $updates = $response['updates'];
        $last = current($updates);
        $first = end($updates);
        debug('Count: '.number_format($c = count($updates)));
        $this->assertLessThan(101, $c);

        $previous = null;
        foreach($updates as $update) {
            $this->assertGreaterThanOrEqual(strtotime($random_past), strtotime($update['time']));
            if(!$previous) {
                $previous = $update;
                continue;
            }
            $this->assertLessThanOrEqual(strtotime($previous['time']), strtotime($update['time']));
            $previous = $update;
        }

        $response = $this->getJson('api/revenues?user_id='.$first_id);
        debug('Revenue: '.number_format($response['revenue'], 2));
        $response->assertStatus(200);
        $response = $this->getJson('api/followers?user_id='.$first_id);
        debug('Followers: '.number_format($response['followers']));
        $response->assertStatus(200);
        $response = $this->getJson('api/sales?user_id='.$first_id);
        debug('Top Sellers');
        debug($response['items']);
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
