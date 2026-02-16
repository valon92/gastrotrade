<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Admin login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email ose fjalëkalimi i gabuar'
            ], 401);
        }

        if (!$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Ju nuk keni akses admin'
            ], 403);
        }

        // Create token for API authentication (optional, for future use)
        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                    'role' => $user->role ?? 'order_manager',
                ],
                'token' => $token
            ]
        ]);
    }

    /**
     * Check if user is authenticated as admin
     */
    public function check(Request $request)
    {
        if ($request->user() && $request->user()->is_admin) {
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'email' => $request->user()->email,
                        'is_admin' => $request->user()->is_admin,
                        'role' => $request->user()->role ?? 'order_manager',
                    ]
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Not authenticated as admin'
        ], 401);
    }

    /**
     * Admin logout
     */
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
