<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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

        // If no filters, get more orders for statistics
        if (!$request->client_id && !$request->status && !$request->search) {
            $orders = $query->limit(1000)->get();
        } else {
            $orders = $query->limit(50)->get();
        }

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function paymentStats()
    {
        // Get all orders that have a valid total_amount (not null and > 0)
        // This excludes test orders or incomplete orders
        $allOrders = Order::whereNotNull('total_amount')
            ->where('total_amount', '>', 0)
            ->get();
        
        // Separate paid and unpaid orders correctly
        $paidOrders = $allOrders->filter(function ($order) {
            return $order->is_paid === true || $order->is_paid === 1;
        });
        
        $unpaidOrders = $allOrders->filter(function ($order) {
            return !$order->is_paid || $order->is_paid === false || $order->is_paid === 0;
        });

        // Calculate totals
        $paidTotal = $paidOrders->sum(function ($order) {
            return (float) $order->total_amount;
        });
        
        $unpaidTotal = $unpaidOrders->sum(function ($order) {
            return (float) $order->total_amount;
        });
        
        $totalAmount = $allOrders->sum(function ($order) {
            return (float) $order->total_amount;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'paid_count' => $paidOrders->count(),
                'unpaid_count' => $unpaidOrders->count(),
                'total_count' => $allOrders->count(),
                'paid_total' => $paidTotal,
                'unpaid_total' => $unpaidTotal,
                'total_amount' => $totalAmount,
            ],
        ]);
    }

    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();
        $customer = $validated['customer'];
        $items = $validated['items'];
        $totals = $validated['totals'];
        $businessName = trim($customer['business_name']);
        $fiscalNumber = strtoupper(preg_replace('/\s+/', '', $customer['fiscal_number']));

        $client = null;
        if (!empty($validated['client_id'])) {
            $client = Client::find($validated['client_id']);
        }

        if (
            !$client &&
            !empty($businessName) &&
            !empty($fiscalNumber)
        ) {
            $client = Client::where('is_active', true)
                ->whereRaw('LOWER(store_name) = ?', [mb_strtolower($businessName)])
                ->where('fiscal_number', $fiscalNumber)
                ->first();
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
            'business_name' => $businessName,
            'fiscal_number' => $fiscalNumber,
            'city' => $customer['city'],
            'phone' => $customer['phone'] ?? null,
            'viber' => $customer['viber'] ?? null,
            'total_items' => $totals['total_items'],
            'subtotal' => $totals['subtotal'] ?? null,
            'discount_amount' => $totals['discount_amount'] ?? 0,
            'discount_type' => $totals['discount_type'] ?? null,
            'discount_value' => $totals['discount_value'] ?? 0,
            'total_amount' => $totals['total_amount'] ?? null,
            'has_vat' => $validated['has_vat'] ?? false,
            'vat_amount' => $validated['vat_amount'] ?? null,
            'amount_before_vat' => $validated['amount_before_vat'] ?? null,
            'summary' => [
                'items' => $items,
                'totals' => $totals,
            ],
            'status' => 'ruajtur',
            'is_paid' => $validated['is_paid'] ?? false,
            'paid_at' => !empty($validated['paid_at']) ? $validated['paid_at'] : null,
        ]);

        foreach ($items as $item) {
            $orderItem = $order->items()->create([
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

            // Update stock if product exists
            if ($item['product_id']) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $quantityToDeduct = $item['sold_by_package'] && $item['pieces_per_package']
                        ? $item['quantity'] * $item['pieces_per_package']
                        : $item['quantity'];

                    $stockBefore = $product->stock_quantity;
                    $product->stock_quantity = max(0, $product->stock_quantity - $quantityToDeduct);
                    $product->save();

                    // Create stock movement
                    StockMovement::create([
                        'product_id' => $product->id,
                        'movement_type' => 'sale',
                        'order_id' => $order->id,
                        'quantity' => -$quantityToDeduct,
                        'unit_cost' => $product->cost_price,
                        'total_cost' => $product->cost_price ? $product->cost_price * $quantityToDeduct : null,
                        'stock_before' => $stockBefore,
                        'stock_after' => $product->stock_quantity,
                        'notes' => 'Shitje - Porosia #' . $order->order_number,
                    ]);
                }
            }
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
        // Normalize empty strings to null for discount fields
        if ($request->has('discount_type') && $request->input('discount_type') === '') {
            $request->merge(['discount_type' => null]);
        }
        if ($request->has('discount_value') && $request->input('discount_value') === '') {
            $request->merge(['discount_value' => null]);
        }
        if ($request->has('discount_amount') && $request->input('discount_amount') === '') {
            $request->merge(['discount_amount' => null]);
        }

        if ($request->has('items')) {
            $normalizedItems = collect($request->input('items'))->map(function ($item) {
                if (array_key_exists('discount_type', $item) && $item['discount_type'] === '') {
                    $item['discount_type'] = null;
                }
                if (array_key_exists('discount_value', $item) && $item['discount_value'] === '') {
                    $item['discount_value'] = null;
                }
                if (array_key_exists('discount_amount', $item) && $item['discount_amount'] === '') {
                    $item['discount_amount'] = null;
                }
                return $item;
            })->toArray();
            $request->merge(['items' => $normalizedItems]);
        }

        $request->validate([
            'status' => ['sometimes', 'string', 'max:50'],
            'customer_name' => ['sometimes', 'string', 'max:255'],
            'business_name' => ['sometimes', 'string', 'max:255'],
            'fiscal_number' => ['sometimes', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:255'],
            'viber' => ['nullable', 'string', 'max:255'],
            'total_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'subtotal' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'discount_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'discount_type' => ['sometimes', 'nullable', 'string', 'in:percentage,fixed'],
            'discount_value' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'has_vat' => ['sometimes', 'boolean'],
            'vat_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'amount_before_vat' => ['sometimes', 'nullable', 'numeric', 'min:0'],
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
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'string', 'in:percentage,fixed'],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
        ]);

        // Handle payment status
        $updateData = $request->only([
            'status',
            'customer_name',
            'business_name',
            'fiscal_number',
            'city',
            'phone',
            'viber',
            'subtotal',
            'discount_amount',
            'discount_type',
            'discount_value',
            'total_amount',
            'has_vat',
            'vat_amount',
            'amount_before_vat',
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

        if (array_key_exists('fiscal_number', $updateData) && $updateData['fiscal_number']) {
            $updateData['fiscal_number'] = strtoupper(preg_replace('/\s+/', '', $updateData['fiscal_number']));
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
            $itemsSubtotal = 0;
            $itemsTotalAfterDiscount = 0;
            
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
                            'discount_amount' => $itemData['discount_amount'] ?? 0,
                            'discount_type' => $itemData['discount_type'] ?? null,
                            'discount_value' => $itemData['discount_value'] ?? 0,
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
                            'discount_amount' => $itemData['discount_amount'] ?? 0,
                            'discount_type' => $itemData['discount_type'] ?? null,
                            'discount_value' => $itemData['discount_value'] ?? 0,
                    ]);
                }
                
                // Calculate totals
                $quantity = $itemData['quantity'];
                if (isset($itemData['sold_by_package']) && $itemData['sold_by_package'] && isset($itemData['pieces_per_package'])) {
                    $totalItems += $quantity * $itemData['pieces_per_package'];
                } else {
                    $totalItems += $quantity;
                }
                
                $itemBaseAmount = 0;
                if (isset($itemData['unit_price']) && $itemData['unit_price']) {
                    if (isset($itemData['sold_by_package']) && $itemData['sold_by_package'] && isset($itemData['pieces_per_package'])) {
                        $itemBaseAmount = $itemData['unit_price'] * $quantity * $itemData['pieces_per_package'];
                    } else {
                        $itemBaseAmount = $itemData['unit_price'] * $quantity;
                    }
                } elseif (isset($itemData['total_price']) && $itemData['total_price']) {
                    $itemBaseAmount = $itemData['total_price'];
                }

                $itemsSubtotal += $itemBaseAmount;

                $itemDiscount = $itemData['discount_amount'] ?? 0;
                $itemTotalValue = $itemData['total_price'] ?? max(0, $itemBaseAmount - $itemDiscount);
                $itemsTotalAfterDiscount += $itemTotalValue;
            }
            
            // Update order totals
            $generalDiscountAmount = $request->input('discount_amount', $order->discount_amount ?? 0) ?? 0;
            $generalDiscountType = $request->input('discount_type', $order->discount_type);
            $generalDiscountValue = $request->input('discount_value', $order->discount_value);

            $finalTotalAmount = max(0, $itemsTotalAfterDiscount - $generalDiscountAmount);

            $order->update([
                'total_items' => $totalItems,
                'subtotal' => $itemsSubtotal > 0 ? $itemsSubtotal : null,
                'discount_amount' => $generalDiscountAmount,
                'discount_type' => $generalDiscountType,
                'discount_value' => $generalDiscountValue,
                'total_amount' => $finalTotalAmount > 0 ? $finalTotalAmount : null,
                'has_vat' => $request->input('has_vat', $order->has_vat),
                'vat_amount' => $request->input('vat_amount', $order->vat_amount),
                'amount_before_vat' => $request->input('amount_before_vat', $order->amount_before_vat),
            ]);
        } elseif ($request->has('total_amount') || $request->hasAny(['discount_amount', 'discount_type', 'discount_value', 'subtotal', 'has_vat', 'vat_amount', 'amount_before_vat'])) {
            // If only total_amount is provided, update it
            $order->update([
                'subtotal' => $request->input('subtotal', $order->subtotal),
                'discount_amount' => $request->input('discount_amount', $order->discount_amount),
                'discount_type' => $request->input('discount_type', $order->discount_type),
                'discount_value' => $request->input('discount_value', $order->discount_value),
                'total_amount' => $request->input('total_amount', $order->total_amount),
                'has_vat' => $request->input('has_vat', $order->has_vat),
                'vat_amount' => $request->input('vat_amount', $order->vat_amount),
                'amount_before_vat' => $request->input('amount_before_vat', $order->amount_before_vat),
            ]);
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

