<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ClientPrice::with(['client', 'product']);

        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $prices = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $prices
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'product_id' => 'required|exists:products,id',
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'allow_piece_sales' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if price already exists for this client-product combination
        $existingPrice = ClientPrice::where('client_id', $request->client_id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingPrice) {
            return response()->json([
                'success' => false,
                'message' => 'Price already exists for this client and product. Use update instead.'
            ], 422);
        }

        $price = ClientPrice::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $price->load(['client', 'product'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $price = ClientPrice::with(['client', 'product'])->find($id);

        if (!$price) {
            return response()->json([
                'success' => false,
                'message' => 'Price not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $price
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $price = ClientPrice::find($id);

        if (!$price) {
            return response()->json([
                'success' => false,
                'message' => 'Price not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'price' => 'sometimes|required|numeric|min:0',
            'notes' => 'nullable|string',
            'allow_piece_sales' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $price->update($request->only(['price', 'notes', 'allow_piece_sales']));

        return response()->json([
            'success' => true,
            'data' => $price->load(['client', 'product'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $price = ClientPrice::find($id);

        if (!$price) {
            return response()->json([
                'success' => false,
                'message' => 'Price not found'
            ], 404);
        }

        $price->delete();

        return response()->json([
            'success' => true,
            'message' => 'Price deleted successfully'
        ]);
    }

    /**
     * Bulk update prices for a client
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'prices' => 'required|array',
            'prices.*.product_id' => 'required|exists:products,id',
            'prices.*.price' => 'nullable|numeric|min:0',
            'prices.*.notes' => 'nullable|string',
            'prices.*.allow_piece_sales' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $clientId = $request->client_id;
        $prices = [];

        foreach ($request->prices as $priceData) {
            // Create/update if price is provided OR allow_piece_sales is set
            $hasPrice = isset($priceData['price']) && $priceData['price'] !== null && $priceData['price'] > 0;
            $hasPieceSales = isset($priceData['allow_piece_sales']) && $priceData['allow_piece_sales'] === true;
            
            if ($hasPrice || $hasPieceSales) {
                $updateData = [];
                
                if ($hasPrice) {
                    $updateData['price'] = $priceData['price'];
                }
                
                if (isset($priceData['notes'])) {
                    $updateData['notes'] = $priceData['notes'];
                }
                
                // Always update allow_piece_sales if provided
                if (isset($priceData['allow_piece_sales'])) {
                    $updateData['allow_piece_sales'] = $priceData['allow_piece_sales'];
                } else {
                    // If not provided, keep existing value or set to false
                    $existing = ClientPrice::where('client_id', $clientId)
                        ->where('product_id', $priceData['product_id'])
                        ->first();
                    $updateData['allow_piece_sales'] = $existing ? $existing->allow_piece_sales : false;
                }
                
                $price = ClientPrice::updateOrCreate(
                    [
                        'client_id' => $clientId,
                        'product_id' => $priceData['product_id']
                    ],
                    $updateData
                );
                
                $prices[] = $price->load('product');
            }
        }

        return response()->json([
            'success' => true,
            'data' => $prices
        ]);
    }
}
