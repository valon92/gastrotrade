<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class ClientAuthController extends Controller
{
    /**
     * Client self-registration: vetëm email + fjalëkalim. Administratori më pas plotëson biznesin dhe çmimet.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:clients,email',
            'password' => 'required|string|min:6|max:255|confirmed',
        ], [
            'email.required' => 'Emaili është i detyrueshëm.',
            'email.email' => 'Vendosni një email të vlefshëm.',
            'email.unique' => 'Ky email është i regjistruar tashmë.',
            'password.confirmed' => 'Fjalëkalimet nuk përputhen.',
            'password.min' => 'Fjalëkalimi duhet të ketë të paktën 6 karaktere.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->email));

        $client = Client::create([
            'email' => $email,
            'name' => $email,
            'store_name' => null,
            'fiscal_number' => null,
            'password' => $request->password,
            'is_active' => true,
        ]);

        $token = $client->createToken('client-cart')->plainTextToken;

        $withRelations = ['prices.product'];
        if (Schema::hasTable('client_locations')) {
            $withRelations[] = 'locations';
        }
        $client->load($withRelations);

        return response()->json([
            'success' => true,
            'message' => 'U regjistruat me sukses. Mund të identifikoheni me këtë email dhe fjalëkalim.',
            'data' => [
                'client' => $client,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * Client login: email + password. Returns Sanctum token and client data (with prices, locations).
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string',
        ], [
            'email.required' => 'Emaili është i detyrueshëm.',
            'email.email' => 'Vendosni një email të vlefshëm.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->email));

        $client = Client::where('is_active', true)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (!$client || empty($client->password) || !Hash::check($request->password, $client->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Emaili ose fjalëkalimi është i gabuar. Regjistrohuni nëse nuk keni llogari.',
            ], 401);
        }

        $client->tokens()->where('name', 'client-cart')->delete();
        $token = $client->createToken('client-cart')->plainTextToken;

        $withRelations = ['prices.product'];
        if (Schema::hasTable('client_locations')) {
            $withRelations[] = 'locations';
        }
        $client->load($withRelations);

        return response()->json([
            'success' => true,
            'data' => [
                'client' => $client,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Current client (from Sanctum token). Only for client tokens.
     */
    public function me(Request $request)
    {
        $user = $request->user();
        if (!$user instanceof Client) {
            return response()->json([
                'success' => false,
                'message' => 'Vetëm klientët e kyqur mund të përdorin këtë endpoint.',
            ], 403);
        }

        $withRelations = ['prices.product'];
        if (Schema::hasTable('client_locations')) {
            $withRelations[] = 'locations';
        }
        $user->load($withRelations);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Client logout: revoke current token.
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if (!$user instanceof Client) {
            return response()->json([
                'success' => false,
                'message' => 'Vetëm klientët e kyqur mund të dalin.',
            ], 403);
        }

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jeni dalë me sukses.',
        ]);
    }
}
