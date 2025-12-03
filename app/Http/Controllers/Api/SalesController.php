<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:50'],
            'is_paid' => ['nullable', 'boolean'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $query = Order::with(['items', 'client'])
            ->whereNotNull('client_id') // Only orders with clients (sales)
            ->latest();

        if ($request->search) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('order_number', 'like', $searchTerm)
                    ->orWhere('customer_name', 'like', $searchTerm)
                    ->orWhere('business_name', 'like', $searchTerm)
                    ->orWhere('phone', 'like', $searchTerm)
                    ->orWhereHas('client', function ($clientQuery) use ($searchTerm) {
                        $clientQuery->where('name', 'like', $searchTerm)
                            ->orWhere('store_name', 'like', $searchTerm)
                            ->orWhere('fiscal_number', 'like', $searchTerm);
                    });
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('is_paid')) {
            $query->where('is_paid', $request->is_paid);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = $request->per_page ?? 50;
        $orders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
        ]);
    }
}

