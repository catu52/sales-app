<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_can_be_created(): void
    {
        $service = \App\Models\Service::factory()->create();

        $this->assertDatabaseHas('services', [
            'item_id' => $service->item_id,
            'required_product_id' => $service->required_product_id,
        ]);
    }
}
