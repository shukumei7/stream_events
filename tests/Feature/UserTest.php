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

class UserTest extends TestCase
{

    // use RefreshDatabase;

    public function test_register(): void
    {

        $count = User::count() + 1;

        $response = $this->withHeaders([
            'X-Header' => 'Value'
        ])->postJson('api/users', [ 'fb_id' => $count, 'fb_name' => 'Test User '.$count]);

        $this->assertContains($response->getStatusCode(), [200, 201]);
        $this->assertGreaterThan(299, Follower::where([ 'user_id' => $response['user_id'] ])->count());
        $this->assertGreaterThan(299, Donation::where([ 'user_id' => $response['user_id'] ])->count());
        $this->assertGreaterThan(299, Subscriber::where([ 'user_id' => $response['user_id'] ])->count());
        $this->assertGreaterThan(299, MerchSale::where([ 'user_id' => $response['user_id'] ])->count());
    }

    public function test_login(): void
    {

        if(empty($user = User::first())) {
            $this->assertEmpty($user);
            return;
        }

        $response = $this->withHeaders([
            'X-Header' => 'Value'
        ])->postJson('api/users', [ 'fb_id' => $user['fb_id'], 'fb_name' => 'Test User 1']);

        $response->assertStatus(200);
    }
}
