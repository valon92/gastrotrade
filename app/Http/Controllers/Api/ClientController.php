<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $withRelations = ['prices.product'];
        
        // Only include locations if table exists
        if (Schema::hasTable('client_locations')) {
            $withRelations[] = 'locations';
        }
        
        $clients = Client::with($withRelations)->orderBy('created_at', 'desc')->get();

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
        $request->merge([
            'store_name' => trim((string) $request->input('store_name')),
            'fiscal_number' => strtoupper(preg_replace('/\s+/', '', (string) $request->input('fiscal_number'))),
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'store_name' => 'required|string|max:255|unique:clients,store_name',
            'fiscal_number' => 'required|string|max:255|unique:clients,fiscal_number',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string',
            'viber' => 'nullable|string',
            'notes' => 'nullable|string',
            'password' => 'nullable|string|min:6|max:255',
            'allow_piece_sales' => 'nullable|boolean',
            'locations' => 'nullable|array',
            'locations.*.unit_name' => 'required_with:locations|string|max:255',
            'locations.*.street_number' => 'nullable|string|max:255',
            'locations.*.phone' => 'nullable|string|max:255',
            'locations.*.viber' => 'nullable|string|max:255',
            'locations.*.notes' => 'nullable|string',
            'locations.*.is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $clientData = $request->except(['locations']);
        $client = Client::create($clientData);

        // Create locations if provided (only if table exists)
        if ($request->has('locations') && is_array($request->locations) && Schema::hasTable('client_locations')) {
            foreach ($request->locations as $locationData) {
                $client->locations()->create($locationData);
            }
        }

        $withRelations = ['prices.product'];
        if (Schema::hasTable('client_locations')) {
            $withRelations[] = 'locations';
        }
        
        return response()->json([
            'success' => true,
            'data' => $client->load($withRelations)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $withRelations = ['prices.product'];
        
        // Only include locations if table exists
        if (Schema::hasTable('client_locations')) {
            $withRelations[] = 'locations';
        }
        
        $client = Client::with($withRelations)->find($id);

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

        if ($request->has('store_name')) {
            $request->merge([
                'store_name' => trim((string) $request->input('store_name')),
            ]);
        }

        if ($request->has('fiscal_number')) {
            $request->merge([
                'fiscal_number' => strtoupper(preg_replace('/\s+/', '', (string) $request->input('fiscal_number'))),
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'store_name' => 'sometimes|required|string|max:255|unique:clients,store_name,' . $id,
            'fiscal_number' => 'sometimes|required|string|max:255|unique:clients,fiscal_number,' . $id,
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string',
            'viber' => 'nullable|string',
            'notes' => 'nullable|string',
            'password' => 'nullable|string|min:6|max:255',
            'is_active' => 'sometimes|boolean',
            'allow_piece_sales' => 'nullable|boolean',
            'locations' => 'nullable|array',
            'locations.*.id' => 'nullable|exists:client_locations,id',
            'locations.*.unit_name' => 'required_with:locations|string|max:255',
            'locations.*.street_number' => 'nullable|string|max:255',
            'locations.*.phone' => 'nullable|string|max:255',
            'locations.*.viber' => 'nullable|string|max:255',
            'locations.*.notes' => 'nullable|string',
            'locations.*.is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $clientData = $request->except(['locations']);
        if (!$request->filled('password')) {
            unset($clientData['password']);
        }
        $client->update($clientData);

        // Sync locations (only if table exists)
        if ($request->has('locations') && Schema::hasTable('client_locations')) {
            $locationIds = [];
            foreach ($request->locations as $locationData) {
                if (isset($locationData['id'])) {
                    // Update existing location
                    $location = $client->locations()->find($locationData['id']);
                    if ($location) {
                        $location->update(collect($locationData)->except('id')->toArray());
                        $locationIds[] = $location->id;
                    }
                } else {
                    // Create new location
                    $newLocation = $client->locations()->create(collect($locationData)->except('id')->toArray());
                    $locationIds[] = $newLocation->id;
                }
            }
            // Delete locations that are not in the request
            $client->locations()->whereNotIn('id', $locationIds)->delete();
        }

        $withRelations = ['prices.product'];
        if (Schema::hasTable('client_locations')) {
            $withRelations[] = 'locations';
        }
        
        return response()->json([
            'success' => true,
            'data' => $client->load($withRelations)
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

        $withRelations = ['prices.product'];
        if (Schema::hasTable('client_locations')) {
            $withRelations[] = 'locations';
        }

        $client = Client::where('is_active', true)
            ->where(function($query) use ($phone, $cleanPhone) {
                $query->where('phone', $phone)
                      ->orWhere('phone', $cleanPhone)
                      ->orWhere('viber', $phone)
                      ->orWhere('viber', $cleanPhone)
                      ->orWhereRaw('REPLACE(REPLACE(phone, " ", ""), "+", "") = ?', [$cleanPhone])
                      ->orWhereRaw('REPLACE(REPLACE(viber, " ", ""), "+", "") = ?', [$cleanPhone]);
            })
            ->with($withRelations)
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
     * Find client by business name and fiscal number; verify password if client has one set.
     */
    public function findByBusinessAndFiscal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string',
            'fiscal_number' => 'required|string',
            'password' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $businessName = strtolower(trim($request->business_name));
        $fiscalNumber = strtoupper(preg_replace('/\s+/', '', trim($request->fiscal_number)));

        $withRelations = ['prices.product'];
        if (Schema::hasTable('client_locations')) {
            $withRelations[] = 'locations';
        }
        
        $client = Client::where('is_active', true)
            ->whereRaw('LOWER(store_name) = ?', [$businessName])
            ->where('fiscal_number', $fiscalNumber)
            ->with($withRelations)
            ->first();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        }

        if (filled($client->password)) {
            $password = $request->input('password', '');
            if (!Hash::check($password, $client->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fjalëkalimi është i gabuar.'
                ], 401);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $client
        ]);
    }
}
