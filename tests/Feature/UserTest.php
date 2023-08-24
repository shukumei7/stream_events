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

    public function test_login(): void
    {

        $count = 3; // User::count() + 1;

        $response = $this->withHeaders([
            'X-Header' => 'Value'
        ])->postJson('api/users', [ 'fb_id' => $count, 'fb_name' => 'Test User '.$count]);

        $this->assertContains($status = $response->getStatusCode(), [200, 201]);
        $this->assertGreaterThan(299, $c = Follower::where([ 'user_id' => $response['user_id'] ])->count());
        $this->assertLessThan(501, $c);
        $this->assertGreaterThan(299, $c = Donation::where([ 'user_id' => $response['user_id'] ])->count());
        $this->assertLessThan(501, $c);
        $this->assertGreaterThan(299, $c = Subscriber::where([ 'user_id' => $response['user_id'] ])->count());
        $this->assertLessThan(501, $c);
        $this->assertGreaterThan(299, $c = MerchSale::where([ 'user_id' => $response['user_id'] ])->count());
        $this->assertLessThan(501, $c);
        
    }

}
