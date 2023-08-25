<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\MerchSale;


class MerchSaleTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void {
        parent::setUp();
        $test = new User;
        $test->name = 'Test User';
        $test->fb_id = 1;
        $test->save();
    }

    public function test_donating() {
        $response = $this->postJson('api/sales');
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'Streamer is required');
        $response = $this->postJson('api/sales', ['streamer_id' => 100]);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'Streamer Not Found');
        $first_user_id = User::first()->value('id');
        $response = $this->postJson('api/sales', ['streamer_id' => $first_user_id]);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'Your name is required');
        $response = $this->postJson('api/sales', ['streamer_id' => $first_user_id, 'name' => 'Me Me']);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'An Item Name is required');
        $response = $this->postJson('api/sales', ['streamer_id' => $first_user_id, 'name' => 'Me Me', 'item_name' => 'Blahblah']);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'How many do you want to buy?');
        $response = $this->postJson('api/sales', ['streamer_id' => $first_user_id, 'name' => 'Me Me', 'item_name' => 'Blahblah', 'amount' => -1]);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'You must buy 1 or more of this item');
        $response = $this->postJson('api/sales', ['streamer_id' => $first_user_id, 'name' => 'Me Me', 'item_name' => 'Blahblah', 'amount' => 'allaN']);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'You have an invalid amount of items');
        $response = $this->postJson('api/sales', ['streamer_id' => $first_user_id, 'name' => 'Me Me', 'item_name' => 'Blahblah', 'amount' => 1]);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'How much do you want to buy this?');
        $response = $this->postJson('api/sales', ['streamer_id' => $first_user_id, 'name' => 'Me Me', 'item_name' => 'Blahblah', 'amount' => 1, 'price' => 'allan']);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'You have an invalid price');
        $response = $this->postJson('api/sales', ['streamer_id' => $first_user_id, 'name' => 'Me Me', 'item_name' => 'Blahblah', 'amount' => 1, 'price' => -1]);
        $response->assertStatus(400);
        $this->assertEquals($response['message'], 'Your price must be more than or equal to $1');
        $response = $this->postJson('api/sales', $td = ['streamer_id' => $first_user_id, 'name' => fake()->name, 'item_name' => 'Blahblah', 'amount' => rand(1, 10), 'price' => rand(1, 100)]);
        $response->assertStatus(201);
        $last = MerchSale::orderBy('id', 'desc')->first();
        $this->assertEquals($td['name'], $last['name']);
        $this->assertEquals($td['amount'], $last['amount']);
        $this->assertEquals($td['price'], $last['price']);
    }
}
