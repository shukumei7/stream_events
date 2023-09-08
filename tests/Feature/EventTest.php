<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\User;
use App\Models\Follower;
use App\Models\Subscriber;
use App\Models\Donation;
use App\Models\MerchSale;
use App\Models\Flag;

class EventTest extends TestCase
{
    // use RefreshDatabase; // DatabaseMigrations;

    public function test_first_registration(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        Follower::truncate();
        Subscriber::truncate();
        Donation::truncate();
        MerchSale::truncate();
        Flag::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $response = $this->postJson('api/users', [ 'fb_id' => '123', 'fb_name' => 'Test User 1', 'fb_token' => 'Random Token']);
        $response->assertOk();
        $this->assertTrue(!empty($response['token']));
        $this->assertTrue(!empty(User::find(1)));
    }

    public function test_not_authenticated(): void
    {
        $response = $this->withHeaders(['Accept' => 'application/json'])->get('api/events');
        $response->assertStatus(401);
        $response = $this->withHeaders(['Accept' => 'application/json'])->get('api/revenues');
        $response->assertStatus(401);
        $response = $this->withHeaders(['Accept' => 'application/json'])->get('api/followers');
        $response->assertStatus(401);
        $response = $this->withHeaders(['Accept' => 'application/json'])->get('api/sales');
        $response->assertStatus(401);
    }

    public function test_events_until_the_end(): void
    {
        $response = $this->actingAs($user = User::find(1))->getJson('api/events');

        $lf = $user->followers()->max('created_at');
        $ld = $user->donations()->max('created_at');
        $ls = $user->subscribers()->max('created_at');
        $lm = $user->sales()->max('created_at');
// dd(compact('lf', 'ld', 'ls', 'lm'));
        $latest_date = max(strtotime($lf), strtotime($ld), strtotime($ls), strtotime($lm));

        $updates = $response['updates'];
        $this->assertLessThan(101, count($updates));
        $this->assertEquals(date(DATE_FORMAT, $latest_date), date(DATE_FORMAT, strtotime(current($updates)['created_at'])));
        $previous = null;
        foreach($updates as $update) {
            if(!$previous) {
                $previous = $update;
                continue;
            }
            $this->assertLessThanOrEqual(strtotime($previous['created_at']), strtotime($update['created_at']));
            $previous = $update;
        }

        while(($response = $this->actingAs($user)->getJson('api/events?before='.$previous['created_at'])) && !empty($response['updates'])) {
            $response->assertStatus(200);
            $updates = $response['updates'];
            $this->assertLessThan(101, count($updates));
            foreach($updates as $update) {
                $this->assertLessThanOrEqual(strtotime($previous['created_at']), strtotime($update['created_at']));
                $previous = $update;
            }
        }

        $response->assertStatus(200);

    }
        
    public function test_future_events(): void
    {      
        $random_past = date(DATE_FORMAT, strtotime('-'.rand(1,10).' days'));
        $response = $this->actingAs(User::find(1))->getJson('api/events?after='.$random_past);
        $response->assertStatus(200);

        debug('Random Past: '.$random_past);
        $updates = $response['updates'];
        $last = current($updates);
        $first = end($updates);
        debug('Count: '.number_format($c = count($updates)));
        $this->assertLessThan(101, $c);

        $previous = null;
        foreach($updates as $update) {
            $this->assertGreaterThanOrEqual(strtotime($random_past), strtotime($update['created_at']));
            if(!$previous) {
                $previous = $update;
                continue;
            }
            $this->assertLessThanOrEqual(strtotime($previous['created_at']), strtotime($update['created_at']));
            $previous = $update;
        }
    }

    public function test_get_top_info(): void
    {
        $response = $this->actingAs($user = User::find(1))->getJson('api/revenues');
        debug('Revenue: '.number_format($response['revenue'], 2));
        $response->assertStatus(200);
        $response = $this->actingAs($user)->getJson('api/followers');
        debug('Followers: '.number_format($response['followers']));
        $response->assertStatus(200);
        $response = $this->actingAs($user)->getJson('api/sales');
        debug('Top Sellers');
        debug($response['items']);
        $response->assertStatus(200);
    }

    public function test_flags() {
        $user = User::find(1);
        $follower_id = $user->followers()->first()->value('id');
        $response = $this->actingAs($user)->postJson('api/flags', ['table' => 'followers', 'table_id' => $follower_id]);
        $this->assertContains($response->getStatusCode(), [201, 204]);
    }
    
}
