<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Follower;


class FollowerTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void {
        parent::setUp();
        $test = new User;
        $test->name = 'Test User';
        $test->fb_id = 1;
        $test->save();
    }

    public function test_following() {
        $response = $this->postJson('api/followers');
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'Streamer is required');
        $response = $this->postJson('api/followers', ['streamer_id' => 100]);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'Streamer Not Found');
        $first_user_id = User::first()->value('id');
        $response = $this->postJson('api/followers', ['streamer_id' => $first_user_id]);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'Your name is required');
        $response = $this->postJson('api/followers', $td = ['streamer_id' => $first_user_id, 'name' => fake()->name]);
        $response->assertStatus(201);
        $this->assertEquals($response['message'], 'Thank you for following us!');
        $last = Follower::orderBy('id', 'desc')->first();
        $this->assertEquals($td['name'], $last['name']);
        $response = $this->postJson('api/followers', $td);
        $response->assertStatus(200);
        $this->assertEquals($response['message'], 'You are already a follower');
    }
}
