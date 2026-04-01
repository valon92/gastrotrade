<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClientAuthController extends Controller
{
    /**
     * Client self-registration: vetëm email + fjalëkalim. Administratori më pas plotëson biznesin dhe çmimet.
     */
    public function register(Request $request)
    {
        $email = strtolower(trim((string) $request->input('email', '')));
        $password = trim((string) $request->input('password', ''));

        $validator = Validator::make([
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $request->input('password_confirmation'),
        ], [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('clients', 'email')->whereNull('deleted_at'),
            ],
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

        $client = new Client();
        $client->email = $email;
        $client->name = $email;
        $client->store_name = null;
        $client->fiscal_number = null;
        $client->is_active = true;
        $client->password = $password;

        try {
            $client->save();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate')) {
                return response()->json([
                    'success' => false,
                    'errors' => ['email' => ['Ky email është i regjistruar tashmë.']],
                ], 422);
            }
            Log::error('Client registration DB error', ['message' => $e->getMessage()]);
            throw $e;
        }

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
        $password = trim((string) $request->password);

        $client = Client::where('is_active', true)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (config('app.debug')) {
            Log::debug('Client login attempt', [
                'email' => $email,
                'client_found' => (bool) $client,
                'has_stored_password' => $client ? !empty($client->getRawOriginal('password')) : false,
            ]);
        }

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Emaili ose fjalëkalimi është i gabuar. Regjistrohuni nëse nuk keni llogari.',
            ], 401);
        }

        $storedPassword = $client->getRawOriginal('password');
        $passwordValid = !empty($storedPassword) && Hash::check($password, $storedPassword);
        if (!$passwordValid && !empty($storedPassword) && $storedPassword === $password) {
            $client->password = $password;
            $client->save();
            $passwordValid = true;
        }
        if (config('app.debug')) {
            Log::debug('Client login password check', ['password_valid' => $passwordValid]);
        }
        if (!$passwordValid) {
            $message = empty($storedPassword)
                ? 'Ky llogari nuk ka fjalëkalim të vendosur. Kontaktoni administratorin.'
                : 'Emaili ose fjalëkalimi është i gabuar. Regjistrohuni nëse nuk keni llogari.';
            return response()->json([
                'success' => false,
                'message' => $message,
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

        $id = $user->getKey();
        try {
            $withRelations = ['prices.product'];
            if (Schema::hasTable('client_locations')) {
                $withRelations[] = 'locations';
            }
            $user->load($withRelations);
        } catch (\Throwable $e) {
            Log::warning('client/me: load relations failed', [
                'client_id' => $id,
                'message' => $e->getMessage(),
            ]);
            $user = Client::query()->find($id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Llogaria nuk u gjet.',
                ], 404);
            }
        }

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

    /**
     * Ndrysho fjalëkalimin e klientit (i kyqur me token). Kërkon fjalëkalimin aktual.
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();
        if (!$user instanceof Client) {
            return response()->json([
                'success' => false,
                'message' => 'Vetëm klientët e kyqur mund të ndryshojnë fjalëkalimin.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|max:255|confirmed',
        ], [
            'current_password.required' => 'Fjalëkalimi aktual është i detyrueshëm.',
            'password.required' => 'Fjalëkalimi i ri është i detyrueshëm.',
            'password.min' => 'Fjalëkalimi i ri duhet të ketë të paktën 6 karaktere.',
            'password.confirmed' => 'Konfirmimi i fjalëkalimit nuk përputhet.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $client = $user;
        $current = trim((string) $request->current_password);
        $newPassword = trim((string) $request->password);

        if (empty($client->password) || !Hash::check($current, $client->password)) {
            return response()->json([
                'success' => false,
                'errors' => ['current_password' => ['Fjalëkalimi aktual është i gabuar.']],
            ], 422);
        }

        $client->password = $newPassword;
        $client->save();

        return response()->json([
            'success' => true,
            'message' => 'Fjalëkalimi u ndryshua me sukses.',
        ]);
    }
}
