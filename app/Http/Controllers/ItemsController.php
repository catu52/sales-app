<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemsController extends Controller
{
    /**
     * Store a new Product.
     * Logic: Create base Item first, then specialized Product record.
     */
    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'is_available' => 'boolean',
            'stock_quantity' => 'required|integer|min:0',
        ]);

        $product = DB::transaction(function () use ($validated) {
            // 1. Create the base Item entry
            // Note: Since Item is abstract, we use the DB facade or a concrete proxy
            $itemId = DB::table('items')->insertGetId([
                'name' => $validated['name'],
                'base_price' => $validated['base_price'],
                'is_available' => $validated['is_available'] ?? true,
                'item_type' => \App\Enums\ItemType::PRODUCT, // Discriminator
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Create the specialized Product entry
            return Product::create([
                'item_id' => $itemId,
                'stock_quantity' => $validated['stock_quantity'],
            ]);
        });

        return response()->json($product->load('item'), 201);
    }

    /**
     * Store a new Service.
     * Logic: Create base Item, then Service entry with optional dependency.
     */
    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'is_available' => 'boolean',
            'required_product_id' => 'nullable|exists:items,id',
        ]);

        $service = DB::transaction(function () use ($validated) {
            // 1. Create base Item
            $itemId = DB::table('items')->insertGetId([
                'name' => $validated['name'],
                'base_price' => $validated['base_price'],
                'is_available' => $validated['is_available'] ?? true,
                'item_type' => \App\Enums\ItemType::SERVICE, //Discriminator
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Create specialized Service entry
            return Service::create([
                'item_id' => $itemId,
                'required_product_id' => $validated['required_product_id'],
            ]);
        });

        return response()->json($service->load('item'), 201);
    }

    public function products(Request $request)
    {
        return Product::get();
    }

    public function services(Request $request)
    {
        return Service::get();
    }

}