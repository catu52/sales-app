<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Services\SaleService;

class SalesController extends Controller
{
    protected $saleService;

    /**
     * Constructor to inject the SaleService for handling business logic related to sales.
     * 
     * @param \App\Services\SaleService $saleService
     * @return void
     */
    public function __construct(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }

     /**
     * GET /api/v1/sales
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        //Query sales information
        $sales = Sale::with(['client', 'details'])->orderBy('created_at', 'desc')->get();
        //Return the sales data as JSON
        return response()->json($sales);
    }

    /**
     * POST /api/v1/sales
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        //Validate input data
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        /**
         * Business logic is handled in the SaleService to keep the controller thin.
         */
        try {
            //Here we call the service App\Services\SaleService to handle the sale recording logic.
            $sale = $this->saleService->recordSale(
                $validated['client_id'], 
                $validated['items']
            );

            //If successful, return the created sale with a success message
            return response()->json([
                'success' => true,
                'data' => $sale,
                'message' => 'Sale recorded successfully.'
            ], 201);

        } catch (\Exception $e) {
            //If an error occurs, return a failure message
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * GET /api/v1/sales/{id}
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id Sale ID to retrieve
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        //Query sale information with related items and client details
        $sale = Sale::with(['client'])->findOrFail($id);
        $sale->details = $this->saleService->getSaleDetails($sale->id);
        //Return the sale data as JSON
        return response()->json($sale);
    }
}
