<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test that a client can be created.
     */
    public function test_client_can_be_created(): void
    {
        $client = \App\Models\Client::factory()->create();

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => $client->name,
            'email' => $client->email,
        ]);
    }
}
