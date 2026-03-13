<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a product can be created and stored in the database.
     */
    public function test_product_can_be_created(): void
    {
        $product = \App\Models\Product::factory()->create();

        $this->assertDatabaseHas('products', [
            'item_id' => $product->item_id,
            'stock_quantity' => $product->stock_quantity,
        ]);
    }

    /**
     *  Test that a product's stock quantity can be updated and reflected in the database.
     */
    public function test_product_can_be_updated(): void
    {
        $product = \App\Models\Product::factory()->create();

        $product->update([
            'stock_quantity' => 50,
        ]);

        $this->assertDatabaseHas('products', [
            'item_id' => $product->item_id,
            'stock_quantity' => 50,
        ]);
    }
}
