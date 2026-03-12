<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Item;
use App\Models\Product;
use App\Models\Service;
use App\Models\Sale;

class SaleService
{
    /**
     * Records a sale for a client with the given items.
     *
     * @param int $clientId
     * @param array $itemsData - Array of ['item_id' => int, 'quantity' => int]
     * @return Sale
     * @throws \Exception if any validation rule is violated
     */
    public function recordSale(int $clientId, array $itemsData)
    {
        return DB::transaction(function () use ($clientId, $itemsData) {
            $today = Carbon::today()->toDateString();
            
            // Rule: Calculate Daily Sequence for this client
            $dailySeq = Sale::where('client_id', $clientId)
                ->where('sale_date', $today)
                ->count() + 1;

            $totalAmount = 0;
            $detailsToInsert = [];

            foreach ($itemsData as $data)
            {
                // Find the discriminator type first to avoid abstract instantiation
                $baseItem = DB::table('items')->select('item_type')->where('id', $data['item_id'])->first();

                if (!$baseItem) {
                    throw new \Exception("Item ID {$data['item_id']} not found.");
                }

                // Instantiate the correct concrete class based on the 'type' column
                $modelClass = $baseItem->item_type === \App\Enums\ItemType::PRODUCT->value ? Product::class : Service::class;

                //Load the item with the correct model to access type-specific relationships and attributes
                $item = $modelClass::where('item_id', $data['item_id'])
                    ->firstOrFail();

                if (!$item || !$item->is_available) {
                    throw new \Exception("Item {$item->name} is currently marked as unavailable.");
                }

                if ($item->item_type === \App\Enums\ItemType::PRODUCT->value) {
                    $this->validateProductRule($item, $clientId, $today, $data['quantity']);
                } else {
                    $this->validateServiceRule($item);
                }

                $lineTotal = $item->base_price * $data['quantity'];
                $totalAmount += $lineTotal;

                //Detail record to insert after sale is created (SaleDetails)
                $detailsToInsert[] = [
                    'item_id' => $item->item_id,
                    'quantity' => $data['quantity'],
                    'unit_price' => $item->price
                ];
            }

            $sale = Sale::create([
                'client_id' => $clientId,
                'sale_date' => $today,
                'client_daily_sales_count' => $dailySeq,
                'total_amount' => $totalAmount
            ]);

            foreach ($detailsToInsert as $detail)
            {
                $sale->details()->create($detail);
                
                // Rule: Update stock if it's a product
                $product = Product::find($detail['item_id']);
                if ($product) {
                    $product->decrement('stock_quantity', $detail['quantity']);
                }
            }

            return $sale->load('details');
        });
    }

    /**
     * Validates the rules for a product before recording a sale.
     *
     * @param Item $item
     * @param int $clientId
     * @param string $date
     * @param int $qty
     * @throws \Exception if any validation rule is violated
     */
    private function validateProductRule(Item $item, int $clientId, string $date, int $qty)
    {

        if (!$item->product) {
            throw new \Exception("Product details missing for item: {$item->name}.");
        }
        
        if ($item->product->stock_quantity < $qty) {
            throw new \Exception("Insufficient stock for product: {$item->name}");
        }

        //Product cannot be sold to more than 3 clients on the same day
        $uniqueClientsCount = DB::table('sale_details')
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->where('sale_details.item_id', $item->id)
            ->where('sales.sale_date', $date)
            ->where('sales.client_id', '!=', $clientId)
            ->distinct('sales.client_id')
            ->count();

        if ($uniqueClientsCount >= 3) {
            throw new \Exception("Product {$item->name} has already reached its daily limit of 3 different clients.");
        }
    }

    /**
     * Validates the rules for a service before recording a sale.
     *
     * @param Item $item
     * @throws \Exception if any validation rule is violated
     */
    private function validateServiceRule(Item $item)
    {
        if (!$item->service) {
            throw new \Exception("Service details missing for item: {$item->name}.");
        }

        if ($item->service->required_product_id) {
            $requiredProduct = Product::find($item->service->required_product_id);
            if (!$requiredProduct || $requiredProduct->stock_quantity <= 0) {
                throw new \Exception("Service {$item->name} requires product ID {$item->service->required_product_id} which is out of stock.");
            }
        }
    }
}