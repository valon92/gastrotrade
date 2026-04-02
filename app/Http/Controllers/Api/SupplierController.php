<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SupplierController extends Controller
{
    private function supplierQuery()
    {
        $query = Supplier::query();

        // Prod mund të mos ketë kolonën deleted_at, ndërsa modeli përdor SoftDeletes.
        // Në atë rast, global scope i soft-deletes e prish query-n (Unknown column).
        if (Schema::hasTable('suppliers') && ! Schema::hasColumn('suppliers', 'deleted_at')) {
            $query->withoutGlobalScope(SoftDeletingScope::class);
        }

        return $query;
    }

    public function index(Request $request)
    {
        if (!Schema::hasTable('suppliers')) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $query = $this->supplierQuery();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
                if (Schema::hasColumn('suppliers', 'contact_person')) {
                    $q->orWhere('contact_person', 'like', '%' . $search . '%');
                }
                if (Schema::hasColumn('suppliers', 'email')) {
                    $q->orWhere('email', 'like', '%' . $search . '%');
                }
                if (Schema::hasColumn('suppliers', 'phone')) {
                    $q->orWhere('phone', 'like', '%' . $search . '%');
                }
            });
        }

        if ($request->has('active_only') && $request->active_only) {
            if (Schema::hasColumn('suppliers', 'is_active')) {
                // Legacy data: is_active mund të jetë NULL. E trajtojmë si aktiv.
                $query->where(function ($q) {
                    $q->where('is_active', true)->orWhereNull('is_active');
                });
            }
        }

        try {
            $suppliers = $query->orderBy('name')->get();
        } catch (\Throwable $e) {
            \Log::warning('Suppliers index failed', ['message' => $e->getMessage()]);
            $suppliers = [];
        }

        return response()->json([
            'success' => true,
            'data' => $suppliers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $supplier = Supplier::create($validated);

        return response()->json([
            'success' => true,
            'data' => $supplier,
        ], 201);
    }

    public function show(Supplier $supplier)
    {
        // Nëse prod s'ka deleted_at, route model binding mund të mos punojë siç duhet
        // në disa raste; prandaj e mbajmë show të thjeshtë me modelin e injektuar.
        return response()->json([
            'success' => true,
            'data' => $supplier->load(['products', 'stockReceipts']),
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $supplier->update($validated);

        return response()->json([
            'success' => true,
            'data' => $supplier,
        ]);
    }

    public function destroy(Supplier $supplier)
    {
        // Check if supplier has products or receipts
        if ($supplier->products()->count() > 0 || $supplier->stockReceipts()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Nuk mund të fshihet prodhuesi sepse ka produkte ose pranime të lidhura.',
            ], 422);
        }

        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Prodhuesi u fshi me sukses.',
        ]);
    }
}
