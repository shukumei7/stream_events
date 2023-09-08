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
        $response->assertStatus(302);
        $response = $this->postJson('api/users', [ 'fb_token' => 'FakeToken']);
        $response->assertStatus(500);
    }

    public function test_login(): void
    {
        $token = 'aEAAE0olGZA06gBO4ZCwQFPiJTYTKuZCXDtcFofc1BTTDub0i1Kkih4uWfnEb7nV52xicQdBitLiP2E4G9vmiJK4ZB8Gwnf8IrWmUTPyGuKrVoIyvYSpQXea8Si7yZCWZCKZB8RYT7lPfFIKOPpb2WTqbZCCZAQg0kcyHreKA7HLQsq1vZB7SFcxHw29k01pxUHEEzZAfZAyGxan1kL0ECDexAerBFI6R1YzZC0j7sqn5hInsSYgq9eB6fqm4OFVeHT0k72YZC4UNA3Eqa5M';
        $response = $this->postJson('api/users', [ 'fb_token' => $token]);
        $this->assertContains($status = $response->getStatusCode(), [200, 201]);
        $response = $this->actingAs(User::find($user_id = $response['user_id']))->get('/api/users');
        $response->assertOk();
        $this->assertGreaterThan(299, $c = Follower::where([ 'user_id' => $user_id ])->count());
        $this->assertLessThan(501, $c);
        $this->assertGreaterThan(299, $c = Donation::where([ 'user_id' => $user_id ])->count());
        $this->assertLessThan(501, $c);
        $this->assertGreaterThan(299, $c = Subscriber::where([ 'user_id' => $user_id ])->count());
        $this->assertLessThan(501, $c);
        $this->assertGreaterThan(299, $c = MerchSale::where([ 'user_id' => $user_id ])->count());
        $this->assertLessThan(501, $c);
        
        
    }

}
