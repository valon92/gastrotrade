<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
    /**
     * List all admin users
     */
    public function index(Request $request)
    {
        // Only full admins can access this
        if (!$request->user() || !$request->user()->isFullAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Ju nuk keni akses në këtë faqe'
            ], 403);
        }

        $query = User::where('is_admin', true);

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        // Remove password from response
        $users = $users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'order_manager',
                'is_admin' => $user->is_admin,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Get a single admin user
     */
    public function show(Request $request, $id)
    {
        // Only full admins can access this
        if (!$request->user() || !$request->user()->isFullAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Ju nuk keni akses në këtë faqe'
            ], 403);
        }

        $user = User::where('is_admin', true)->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Përdoruesi nuk u gjet'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'order_manager',
                'is_admin' => $user->is_admin,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Create a new admin user
     */
    public function store(Request $request)
    {
        // Only full admins can access this
        if (!$request->user() || !$request->user()->isFullAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Ju nuk keni akses në këtë faqe'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,order_manager',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => true,
            'role' => $request->role,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Admin u krijua me sukses',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_admin' => $user->is_admin,
            ]
        ], 201);
    }

    /**
     * Update an admin user
     */
    public function update(Request $request, $id)
    {
        // Only full admins can access this
        if (!$request->user() || !$request->user()->isFullAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Ju nuk keni akses në këtë faqe'
            ], 403);
        }

        $user = User::where('is_admin', true)->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Përdoruesi nuk u gjet'
            ], 404);
        }

        // Prevent self-deletion of admin role
        if ($request->user()->id == $id && $request->has('role') && $request->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Nuk mund ta ndryshoni rolin tuaj'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'sometimes|nullable|string|min:8',
            'role' => 'sometimes|required|in:admin,order_manager',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = [];
        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }
        if ($request->has('email')) {
            $updateData['email'] = $request->email;
        }
        if ($request->has('password') && $request->password) {
            $updateData['password'] = Hash::make($request->password);
        }
        if ($request->has('role')) {
            $updateData['role'] = $request->role;
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Admin u përditësua me sukses',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_admin' => $user->is_admin,
            ]
        ]);
    }

    /**
     * Delete an admin user
     */
    public function destroy(Request $request, $id)
    {
        // Only full admins can access this
        if (!$request->user() || !$request->user()->isFullAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Ju nuk keni akses në këtë faqe'
            ], 403);
        }

        $user = User::where('is_admin', true)->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Përdoruesi nuk u gjet'
            ], 404);
        }

        // Prevent self-deletion
        if ($request->user()->id == $id) {
            return response()->json([
                'success' => false,
                'message' => 'Nuk mund ta fshini vetveten'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Admin u fshi me sukses'
        ]);
    }
}

