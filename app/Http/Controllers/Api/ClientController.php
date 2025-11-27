<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = Client::with('prices.product')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $clients
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'store_name' => 'required|string|max:255|unique:clients,store_name',
            'fiscal_number' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string',
            'viber' => 'nullable|string',
            'notes' => 'nullable|string',
            'allow_piece_sales' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $client = Client::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $client->load('prices.product')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $client = Client::with('prices.product')->find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $client
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'store_name' => 'sometimes|required|string|max:255|unique:clients,store_name,' . $id,
            'fiscal_number' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string',
            'viber' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'allow_piece_sales' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $client->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $client->load('prices.product')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        }

        $client->delete();

        return response()->json([
            'success' => true,
            'message' => 'Client deleted successfully'
        ]);
    }

    /**
     * Find client by phone number (legacy support)
     */
    public function findByPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $phone = $request->phone;
        // Clean phone number (remove spaces, +, etc.)
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        $client = Client::where('is_active', true)
            ->where(function($query) use ($phone, $cleanPhone) {
                $query->where('phone', $phone)
                      ->orWhere('phone', $cleanPhone)
                      ->orWhere('viber', $phone)
                      ->orWhere('viber', $cleanPhone)
                      ->orWhereRaw('REPLACE(REPLACE(phone, " ", ""), "+", "") = ?', [$cleanPhone])
                      ->orWhereRaw('REPLACE(REPLACE(viber, " ", ""), "+", "") = ?', [$cleanPhone]);
            })
            ->with('prices.product')
            ->first();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $client
        ]);
    }

    /**
     * Find client by business name
     */
    public function findByBusinessName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $businessName = trim($request->business_name);

        $client = Client::where('is_active', true)
            ->where('store_name', $businessName)
            ->with('prices.product')
            ->first();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $client
        ]);
    }
}
