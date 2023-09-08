<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\User;
use App\Models\Follower;
use App\Models\Subscriber;
use App\Models\Donation;
use App\Models\MerchSale;

class UserTest extends TestCase
{

    use RefreshDatabase;
    // use DatabaseMigrations;

    public function test_bad_registration(): void
    {
        $count = 1;
        $response = $this->postJson('api/users');
        $response->assertStatus(400);
        $response = $this->postJson('api/users', [ 'fb_name' => 'Test User '.$count]);
        $response->assertStatus(400);
        $response = $this->postJson('api/users', [ 'fb_id' => $count]);
        $response->assertStatus(400);
    }

    public function test_login(): void
    {
        $count = 3; // User::count() + 1;
        $response = $this->postJson('api/users', [ 'fb_id' => $count, 'fb_name' => 'Test User '.$count, 'fb_token' => 'Random Token']);
        $this->assertContains($status = $response->getStatusCode(), [200, 201]);
        $this->assertTrue(!empty($response['token']));
        $this->assertGreaterThan(299, $c = Follower::where([ 'user_id' => $response['user_id'] ])->count());
        $this->assertLessThan(501, $c);
        $this->assertGreaterThan(299, $c = Donation::where([ 'user_id' => $response['user_id'] ])->count());
        $this->assertLessThan(501, $c);
        $this->assertGreaterThan(299, $c = Subscriber::where([ 'user_id' => $response['user_id'] ])->count());
        $this->assertLessThan(501, $c);
        $this->assertGreaterThan(299, $c = MerchSale::where([ 'user_id' => $response['user_id'] ])->count());
        $this->assertLessThan(501, $c);
        $response = $this->actingAs(User::find($response['user_id']))->get('/api/users');
        $response->assertOk();
        
    }

}
