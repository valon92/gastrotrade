<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function adminIndex(Request $request)
    {
        $request->validate([
            'client_id' => ['nullable', 'exists:clients,id'],
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $query = Order::with(['items', 'client'])->latest();

        if ($request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('order_number', 'like', $searchTerm)
                    ->orWhere('customer_name', 'like', $searchTerm)
                    ->orWhere('business_name', 'like', $searchTerm)
                    ->orWhere('phone', 'like', $searchTerm);
            });
        }

        $orders = $query->limit(50)->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();
        $customer = $validated['customer'];
        $items = $validated['items'];
        $totals = $validated['totals'];

        $client = null;
        if (!empty($validated['client_id'])) {
            $client = Client::find($validated['client_id']);
        }

        if (!$client && !empty($customer['phone'])) {
            $client = Client::where('phone', $customer['phone'])
                ->orWhere('viber', $customer['phone'])
                ->first();
        }

        $order = Order::create([
            'client_id' => $client?->id,
            'order_number' => $this->generateOrderNumber(),
            'customer_name' => $customer['name'],
            'business_name' => $customer['business_name'],
            'city' => $customer['city'],
            'phone' => $customer['phone'] ?? null,
            'viber' => $customer['viber'] ?? null,
            'total_items' => $totals['total_items'],
            'subtotal' => $totals['subtotal'] ?? null,
            'discount_amount' => $totals['discount_amount'] ?? 0,
            'discount_type' => $totals['discount_type'] ?? null,
            'discount_value' => $totals['discount_value'] ?? 0,
            'total_amount' => $totals['total_amount'] ?? null,
            'summary' => [
                'items' => $items,
                'totals' => $totals,
            ],
            'status' => 'ruajtur',
            'is_paid' => $validated['is_paid'] ?? false,
            'paid_at' => !empty($validated['paid_at']) ? $validated['paid_at'] : null,
        ]);

        foreach ($items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'product_name' => $item['name'],
                'quantity' => $item['quantity'],
                'sold_by_package' => $item['sold_by_package'],
                'pieces_per_package' => $item['pieces_per_package'] ?? null,
                'unit_price' => $item['unit_price'] ?? null,
                'total_price' => $item['total_price'] ?? null,
                'discount_amount' => $item['discount_amount'] ?? 0,
                'discount_type' => $item['discount_type'] ?? null,
                'discount_value' => $item['discount_value'] ?? 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $order->load('items'),
        ], 201);
    }

    public function history(Request $request)
    {
        $request->validate([
            'phone' => ['nullable', 'string'],
            'client_id' => ['nullable', 'exists:clients,id'],
        ]);

        if (!$request->phone && !$request->client_id) {
            return response()->json([
                'success' => false,
                'message' => 'Kërkohet telefoni ose klienti për të marrë historinë.',
            ], 422);
        }

        $query = Order::with('items')->latest();

        if ($request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->phone) {
            $query->where('phone', $request->phone);
        }

        // Use distinct to prevent duplicates
        $orders = $query->distinct()->limit(10)->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function show(Order $order, Request $request)
    {
        $request->validate([
            'phone' => ['nullable', 'string'],
        ]);

        if ($request->phone && $order->phone !== $request->phone) {
            return response()->json([
                'success' => false,
                'message' => 'Nuk keni qasje në këtë porosi.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $order->load('items'),
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['sometimes', 'string', 'max:50'],
            'customer_name' => ['sometimes', 'string', 'max:255'],
            'business_name' => ['sometimes', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:255'],
            'viber' => ['nullable', 'string', 'max:255'],
            'total_amount' => ['sometimes', 'numeric', 'min:0'],
            'is_paid' => ['sometimes', 'boolean'],
            'items' => ['sometimes', 'array'],
            'items.*.id' => ['nullable', 'exists:order_items,id'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_name' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.sold_by_package' => ['sometimes', 'boolean'],
            'items.*.pieces_per_package' => ['nullable', 'integer'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.total_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        // Handle payment status
        $updateData = $request->only([
            'status',
            'customer_name',
            'business_name',
            'city',
            'phone',
            'viber',
            'is_paid',
        ]);

        // If marking as paid, set paid_at timestamp
        if ($request->has('is_paid')) {
            if ($request->input('is_paid') && !$order->is_paid) {
                $updateData['paid_at'] = now();
            } elseif (!$request->input('is_paid') && $order->is_paid) {
                $updateData['paid_at'] = null;
            }
        }

        // Update order basic info
        $order->update($updateData);

        // Update items if provided
        if ($request->has('items')) {
            $items = $request->input('items');
            $existingItemIds = collect($items)->pluck('id')->filter()->toArray();
            
            // Delete items that are not in the new list
            $order->items()->whereNotIn('id', $existingItemIds)->delete();
            
            // Update or create items
            $totalItems = 0;
            $totalAmount = 0;
            
            foreach ($items as $itemData) {
                if (isset($itemData['id']) && $itemData['id']) {
                    // Update existing item
                    $item = $order->items()->find($itemData['id']);
                    if ($item) {
                        $item->update([
                            'product_id' => $itemData['product_id'] ?? $item->product_id,
                            'product_name' => $itemData['product_name'],
                            'quantity' => $itemData['quantity'],
                            'sold_by_package' => $itemData['sold_by_package'] ?? false,
                            'pieces_per_package' => $itemData['pieces_per_package'] ?? null,
                            'unit_price' => $itemData['unit_price'] ?? null,
                            'total_price' => $itemData['total_price'] ?? null,
                        ]);
                    }
                } else {
                    // Create new item
                    $order->items()->create([
                        'product_id' => $itemData['product_id'] ?? null,
                        'product_name' => $itemData['product_name'],
                        'quantity' => $itemData['quantity'],
                        'sold_by_package' => $itemData['sold_by_package'] ?? false,
                        'pieces_per_package' => $itemData['pieces_per_package'] ?? null,
                        'unit_price' => $itemData['unit_price'] ?? null,
                        'total_price' => $itemData['total_price'] ?? null,
                    ]);
                }
                
                // Calculate totals
                $quantity = $itemData['quantity'];
                if (isset($itemData['sold_by_package']) && $itemData['sold_by_package'] && isset($itemData['pieces_per_package'])) {
                    $totalItems += $quantity * $itemData['pieces_per_package'];
                } else {
                    $totalItems += $quantity;
                }
                
                if (isset($itemData['total_price']) && $itemData['total_price']) {
                    $totalAmount += $itemData['total_price'];
                } elseif (isset($itemData['unit_price']) && $itemData['unit_price']) {
                    if (isset($itemData['sold_by_package']) && $itemData['sold_by_package'] && isset($itemData['pieces_per_package'])) {
                        $totalAmount += $itemData['unit_price'] * $quantity * $itemData['pieces_per_package'];
                    } else {
                        $totalAmount += $itemData['unit_price'] * $quantity;
                    }
                }
            }
            
            // Update order totals
            $order->update([
                'total_items' => $totalItems,
                'total_amount' => $totalAmount > 0 ? $totalAmount : null,
            ]);
        } elseif ($request->has('total_amount')) {
            // If only total_amount is provided, update it
            $order->update(['total_amount' => $request->input('total_amount')]);
        }

        return response()->json([
            'success' => true,
            'data' => $order->load('items'),
        ]);
    }

    public function destroy(Order $order)
    {
        $order->items()->delete();
        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Porosia u fshi me sukses',
        ]);
    }

    private function generateOrderNumber(): string
    {
        $datePart = now()->format('Ymd');
        $randomPart = Str::upper(Str::random(4));
        $count = Order::whereDate('created_at', now()->toDateString())->count() + 1;
        $countPart = str_pad((string) $count, 3, '0', STR_PAD_LEFT);

        return "GT-{$datePart}-{$countPart}{$randomPart}";
    }
}

