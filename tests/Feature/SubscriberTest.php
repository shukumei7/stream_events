<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Subscriber;


class SubscriberTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void {
        parent::setUp();
        $test = new User;
        $test->name = 'Test User';
        $test->fb_id = 1;
        $test->save();
    }

    public function test_subscribing() {
        $response = $this->postJson('api/subscribers');
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'Streamer is required');
        $response = $this->postJson('api/subscribers', ['streamer_id' => 100]);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'Streamer Not Found');
        $first_user_id = User::first()->value('id');
        $response = $this->postJson('api/subscribers', ['streamer_id' => $first_user_id]);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'Your name is required');
        $response = $this->postJson('api/subscribers', $td = ['streamer_id' => $first_user_id, 'name' => 'Me Me']);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'Subscription Tier is required');
        $response = $this->postJson('api/subscribers', $td = ['streamer_id' => $first_user_id, 'name' => 'Me Me', 'tier' => 'something']);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'Subscription Tiers are 1, 2, or 3');
        $response = $this->postJson('api/subscribers', $td = ['streamer_id' => $first_user_id, 'name' => fake()->name, 'tier' => rand(1,3)]);
        $response->assertStatus(201);
        $last = Subscriber::orderBy('id', 'desc')->first();
        $this->assertEquals($td['name'], $last['name']);
        $this->assertEquals($td['tier'], $last['tier']);
    }
}
