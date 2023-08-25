<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Donation;


class DonationTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void {
        parent::setUp();
        $count = 1; // User::count() + 1;
        $response = $this->postJson('api/users', [ 'fb_id' => $count, 'fb_name' => 'Test User '.$count]);
    }

    public function test_donating() {
        $response = $this->postJson('api/donations');
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'Streamer is required');
        $response = $this->postJson('api/donations', ['streamer_id' => 100]);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'Streamer Not Found');
        $first_user_id = User::first()->value('id');
        $response = $this->postJson('api/donations', ['streamer_id' => $first_user_id]);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'Your name is required');
        $response = $this->postJson('api/donations', $td = ['streamer_id' => $first_user_id, 'name' => 'Me Me']);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'Currency is required');
        $response = $this->postJson('api/donations', $td = ['streamer_id' => $first_user_id, 'name' => 'Me Me', 'currency' => 'PHP']);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'We only accept CAD or USD');
        $response = $this->postJson('api/donations', $td = ['streamer_id' => $first_user_id, 'name' => 'Me Me', 'currency' => 'CAD']);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'An amount is required');
        $response = $this->postJson('api/donations', $td = ['streamer_id' => $first_user_id, 'name' => 'Me Me', 'currency' => 'CAD', 'amount' => -1]);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'Your amount must be more than or equal to $1');
        $response = $this->postJson('api/donations', $td = ['streamer_id' => $first_user_id, 'name' => 'Me Me', 'currency' => 'CAD', 'amount' => 'allaN']);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'Your amount must be more than or equal to $1');

        $current = Donation::orderBy('id', 'desc')->first();

        $response = $this->postJson('api/donations', $td = ['streamer_id' => $first_user_id, 'name' => $fn = fake()->name, 'currency' => 'CAD', 'amount' => $fa = rand(1, 10)]);
        $response->assertStatus(201);
        $last = Donation::orderBy('id', 'desc')->first();
        $this->assertEquals($td['name'], $last['name']);
        $this->assertEquals($td['amount'], $last['amount']);
        $this->assertGreaterThan($current['id'], $last['id']);
    }
}
