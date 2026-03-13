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
     * List all items with their specific attributes.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        // Fetch all items with their specific attributes using a single query with joins
        $items = DB::table('items')
            ->leftJoin('products', 'items.id', '=', 'products.item_id')
            ->leftJoin('services', 'items.id', '=', 'services.item_id')
            ->select(
                'items.id',
                'items.name',
                'items.price',
                'items.is_available',
                DB::raw("CASE 
                    WHEN items.item_type = '" . \App\Enums\ItemType::PRODUCT->value . "' THEN 'product' 
                    WHEN items.item_type = '" . \App\Enums\ItemType::SERVICE->value . "' THEN 'service' 
                    ELSE 'unknown' END AS item_type"),
                'products.stock_quantity',
                'services.required_product_id'
            )
            ->get();
        return response()->json($items);
    }

    /**
     * Store a new Product.
     * Logic: Create base Item first, then specialized Product record.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeProduct(Request $request): \Illuminate\Http\JsonResponse
    {
        //Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_available' => 'boolean',
            'stock_quantity' => 'required|integer|min:0',
        ]);

        $product = DB::transaction(function () use ($validated) {
            // Create the base Item entry
            // Since Item is abstract, we use the DB facade or a concrete proxy
            $itemId = DB::table('items')->insertGetId([
                'name' => $validated['name'],
                'price' => $validated['price'],
                'is_available' => $validated['is_available'] ?? true,
                'item_type' => \App\Enums\ItemType::PRODUCT->value, // Discriminator
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Create the specialized Product entry
            return Product::create([
                'item_id' => $itemId,
                'stock_quantity' => $validated['stock_quantity'],
            ]);
        });

        return response()->json($product->refresh(), 201);
    }

    /**
     * Store a new Service.
     * Logic: Create base Item, then Service entry with optional dependency.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeService(Request $request): \Illuminate\Http\JsonResponse
    {
        //Validate input, including optional required_product_id that must exist in items table
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_available' => 'boolean',
            'required_product_id' => 'nullable|exists:items,id',
        ]);

        $service = DB::transaction(function () use ($validated) {
            // Create base Item
            $itemId = DB::table('items')->insertGetId([
                'name' => $validated['name'],
                'price' => $validated['price'],
                'is_available' => $validated['is_available'] ?? true,
                'item_type' => \App\Enums\ItemType::SERVICE->value, //Discriminator
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Create specialized Service entry
            return Service::create([
                'item_id' => $itemId,
                'required_product_id' => $validated['required_product_id'],
            ]);
        });

        return response()->json($service->refresh(), 201);
    }

    /**
     * Update an existing Product.
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id Item ID to update
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProduct(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $product = Product::where('item_id', $id)->firstOrFail();
        
        $validated = $request->validate([
            'name' => 'string|max:255',
            'price' => 'numeric|min:0',
            'is_available' => 'boolean',
            'stock_quantity' => 'integer|min:0',
        ]);

        DB::transaction(function () use ($product, $validated) {
            // Update Base Item fields
            DB::table('items')->where('id', $product->item_id)->update(
                collect($validated)->only(['name', 'price', 'is_available'])->toArray()
            );

            // Update Product specific fields
            if (isset($validated['stock_quantity'])) {
                $product->update(['stock_quantity' => $validated['stock_quantity']]);
            }
        });

        return response()->json($product->fresh(['item']));
    }

    /**
     * Update an existing Service.
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id Item ID to update
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateService(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $service = Service::where('item_id', $id)->firstOrFail();

        $validated = $request->validate([
            'name' => 'string|max:255',
            'price' => 'numeric|min:0',
            'is_available' => 'boolean',
            'required_product_id' => 'nullable|exists:items,id',
        ]);

        DB::transaction(function () use ($service, $validated) {
            DB::table('items')->where('id', $service->item_id)->update(
                collect($validated)->only(['name', 'price', 'is_available'])->toArray()
            );

            if (array_key_exists('required_product_id', $validated)) {
                $service->update(['required_product_id' => $validated['required_product_id']]);
            }
        });

        return response()->json($service->fresh(['item']));
    }

    /**
     * Delete an Item (Cascades to child table via DB foreign keys).
     * 
     * @param int $id Item ID to delete
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id): \Illuminate\Http\JsonResponse
    {
        // Deleting from the base table will delete from products/services 
        // IF your migration has ->onDelete('cascade')
        $deleted = DB::table('items')->where('id', $id)->delete();

        if ($deleted) {
            return response()->json(['message' => 'Item deleted successfully']);
        }

        return response()->json(['error' => 'Item not found'], 404);
    }

}