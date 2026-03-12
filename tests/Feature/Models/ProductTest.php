<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_be_created(): void
    {
        $product = \App\Models\Product::factory()->create();

        $this->assertDatabaseHas('products', [
            'item_id' => $product->item_id,
            'stock_quantity' => $product->stock_quantity,
        ]);
    }
}
