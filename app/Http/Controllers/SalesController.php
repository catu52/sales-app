<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Services\SaleService;

class SalesController extends Controller
{
    protected $saleService;

    public function __construct(SaleService $saleService) {
        $this->saleService = $saleService;
    }

    /**
     * POST /api/sales
     */
    public function store(Request $request) {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $sale = $this->saleService->recordSale(
                $validated['client_id'], 
                $validated['items']
            );

            return response()->json([
                'success' => true,
                'data' => $sale,
                'message' => 'Sale recorded successfully.'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * GET /api/sales/{id}
     */
    public function show($id) {
        $sale = Sale::with(['details.item'])->findOrFail($id);
        return response()->json($sale);
    }

    public function index()
    {
        // Rule: Query sales information
        return Sale::with(['details.item', 'client'])->orderBy('created_at', 'desc')->get();
    }
}
