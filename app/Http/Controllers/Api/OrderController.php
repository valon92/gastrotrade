<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Mail\OrderConfirmationMail;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function adminIndex(Request $request)
    {
        $request->validate([
            'client_id' => ['nullable', 'exists:clients,id'],
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $query = Order::with(['items.product', 'client'])->latest();

        if ($request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $searchTerm = '%'.$request->search.'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('order_number', 'like', $searchTerm)
                    ->orWhere('customer_name', 'like', $searchTerm)
                    ->orWhere('business_name', 'like', $searchTerm)
                    ->orWhere('phone', 'like', $searchTerm);
            });
        }

        // If no filters, get more orders for statistics
        if (! $request->client_id && ! $request->status && ! $request->search) {
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
            return ! $order->is_paid || $order->is_paid === false || $order->is_paid === 0;
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
        if (! empty($validated['client_id'])) {
            $client = Client::find($validated['client_id']);
        }

        if (
            ! $client &&
            ! empty($businessName) &&
            ! empty($fiscalNumber)
        ) {
            $client = Client::where('is_active', true)
                ->whereRaw('LOWER(store_name) = ?', [mb_strtolower($businessName)])
                ->where('fiscal_number', $fiscalNumber)
                ->first();
        }

        if (! $client && ! empty($customer['phone'])) {
            $client = Client::where('phone', $customer['phone'])
                ->orWhere('viber', $customer['phone'])
                ->first();
        }

        // Validate client_location_id if provided and get location data
        $clientLocationId = null;
        $locationData = [
            'location_unit_name' => null,
            'location_street_number' => null,
            'location_phone' => null,
            'location_viber' => null,
            'location_city' => null,
        ];

        if (! empty($validated['client_location_id'])) {
            // Verify that the location belongs to the client
            if ($client && Schema::hasTable('client_locations')) {
                $location = \App\Models\ClientLocation::where('id', $validated['client_location_id'])
                    ->where('client_id', $client->id)
                    ->whereNull('deleted_at')
                    ->first();
                if ($location) {
                    $clientLocationId = $location->id;
                    // Store location data in order
                    $locationData = [
                        'location_unit_name' => $location->unit_name,
                        'location_street_number' => $location->street_number,
                        'location_phone' => $location->phone,
                        'location_viber' => $location->viber,
                        'location_city' => $location->notes, // notes field stores city/location
                    ];
                }
            }
        }

        // Use provided order_number or generate new one
        $orderNumber = ! empty($validated['order_number'])
            ? $validated['order_number']
            : $this->generateOrderNumber();

        $orderData = [
            'client_id' => $client?->id,
            'client_location_id' => $clientLocationId,
            'customer_name' => $customer['name'],
            'business_name' => $businessName,
            'fiscal_number' => $fiscalNumber,
            'city' => $customer['city'],
            'phone' => trim((string) ($customer['phone'] ?? '')),
            'viber' => isset($customer['viber']) && $customer['viber'] !== '' && $customer['viber'] !== null
                ? trim((string) $customer['viber'])
                : null,
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
            'paid_at' => ! empty($validated['paid_at']) ? $validated['paid_at'] : null,
        ];

        // Only add location fields if columns exist
        if (Schema::hasColumn('orders', 'location_unit_name')) {
            $orderData['location_unit_name'] = $locationData['location_unit_name'];
            $orderData['location_street_number'] = $locationData['location_street_number'];
            $orderData['location_phone'] = $locationData['location_phone'];
            $orderData['location_viber'] = $locationData['location_viber'];
            $orderData['location_city'] = $locationData['location_city'];
        }

        if (! Schema::hasColumn('orders', 'client_location_id')) {
            unset($orderData['client_location_id']);
        }

        // Vetëm kolona që ekzistojnë në DB (prod mund të ketë migracione të pjesëshme)
        $orderData = self::filterToTableColumns('orders', $orderData);

        try {
            $order = null;
            $currentOrderNumber = $orderNumber;
            $maxAttempts = 5;

            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                $payload = array_merge($orderData, ['order_number' => $currentOrderNumber]);

                try {
                    $order = DB::transaction(function () use ($payload, $items) {
                        $order = Order::create($payload);

                        foreach ($items as $item) {
                            $itemPayload = self::filterToTableColumns('order_items', [
                                'product_id' => $item['product_id'] ?? null,
                                'product_name' => $item['name'],
                                'quantity' => $item['quantity'],
                                'sold_by_package' => $item['sold_by_package'],
                                'pieces_per_package' => $item['pieces_per_package'] ?? null,
                                'unit_type' => $item['unit_type'] ?? null,
                                'unit_price' => $item['unit_price'] ?? null,
                                'total_price' => $item['total_price'] ?? null,
                                'discount_amount' => $item['discount_amount'] ?? 0,
                                'discount_type' => $item['discount_type'] ?? null,
                                'discount_value' => $item['discount_value'] ?? 0,
                            ]);
                            $order->items()->create($itemPayload);

                            if (! empty($item['product_id'])) {
                                $product = Product::lockForUpdate()->find($item['product_id']);
                                if ($product && Schema::hasColumn('products', 'stock_quantity')) {
                                    $quantityToDeduct = $item['sold_by_package'] && $item['pieces_per_package']
                                        ? (int) $item['quantity'] * (int) $item['pieces_per_package']
                                        : (int) $item['quantity'];

                                    $stockBefore = (int) ($product->stock_quantity ?? 0);

                                    $stockAfter = max(0, $stockBefore - $quantityToDeduct);
                                    $product->stock_quantity = $stockAfter;
                                    $product->save();

                                    $effectiveDeduct = min($quantityToDeduct, $stockBefore);

                                    if ($effectiveDeduct > 0 && Schema::hasTable('stock_movements')) {
                                        StockMovement::create(self::filterToTableColumns('stock_movements', [
                                            'product_id' => $product->id,
                                            'movement_type' => 'sale',
                                            'stock_receipt_id' => null,
                                            'order_id' => $order->id,
                                            'quantity' => -$effectiveDeduct,
                                            'unit_cost' => $product->cost_price,
                                            'total_cost' => $product->cost_price ? $product->cost_price * $effectiveDeduct : null,
                                            'stock_before' => $stockBefore,
                                            'stock_after' => $stockAfter,
                                            'notes' => 'Shitje - Porosia #'.$order->order_number,
                                        ]));
                                    }

                                    $missing = $quantityToDeduct - $stockBefore;
                                    if ($missing > 0 && Schema::hasTable('stock_movements')) {
                                        $missingCp = $missing;

                                        $missingPackages = null;
                                        if (! empty($item['sold_by_package']) && ! empty($item['pieces_per_package'])) {
                                            $missingPackages = $missingCp / $item['pieces_per_package'];
                                        }

                                        $categoryName = $product->category?->name;

                                        $notes = 'Mungesë stoku - '.$product->name;
                                        if ($categoryName) {
                                            $notes .= ' - '.$categoryName;
                                        }

                                        if ($missingPackages !== null) {
                                            $notes .= sprintf(
                                                ' -%s komplete -%dcp',
                                                rtrim(rtrim(number_format($missingPackages, 2, '.', ''), '0'), '.'),
                                                $missingCp
                                            );
                                        } else {
                                            $notes .= sprintf(' -%dcp', $missingCp);
                                        }

                                        StockMovement::create(self::filterToTableColumns('stock_movements', [
                                            'product_id' => $product->id,
                                            'movement_type' => 'shortage',
                                            'stock_receipt_id' => null,
                                            'order_id' => $order->id,
                                            'quantity' => -$missingCp,
                                            'unit_cost' => $product->cost_price,
                                            'total_cost' => $product->cost_price ? $product->cost_price * $missingCp : null,
                                            'stock_before' => $stockAfter,
                                            'stock_after' => $stockAfter,
                                            'notes' => $notes,
                                        ]));
                                    }
                                }
                            }
                        }

                        return $order;
                    });

                    break;
                } catch (QueryException $e) {
                    if ($attempt >= $maxAttempts || ! $this->isDuplicateOrderNumberConstraint($e)) {
                        throw $e;
                    }
                    \Log::warning('Order store: duplicate order_number, retrying', [
                        'attempt' => $attempt,
                        'order_number' => $currentOrderNumber,
                    ]);
                    $currentOrderNumber = $this->generateUniqueOrderNumberFallback();
                }
            }

            if (! $order) {
                throw new \RuntimeException('Nuk u gjenerua dot numër unik porosie.');
            }

            $order->load(['items.product']);
            if (
                \Illuminate\Support\Facades\Schema::hasTable('client_locations')
                && \Illuminate\Support\Facades\Schema::hasColumn('orders', 'client_location_id')
            ) {
                try {
                    $order->load('clientLocation');
                } catch (\Throwable $e) {
                    \Log::warning('Order store: clientLocation load failed', ['message' => $e->getMessage()]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $order,
            ], 201);
        } catch (\Throwable $e) {
            \Log::error('Order store failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'previous' => $e->getPrevious() ? $e->getPrevious()->getMessage() : null,
                'trace' => substr($e->getTraceAsString(), 0, 6000),
            ]);

            return response()->json([
                'success' => false,
                'message' => config('app.debug')
                    ? $e->getMessage()
                    : 'Ruajtja e porosisë dështoi. Ju lutem provoni përsëri ose kontaktoni mbështetjen.',
            ], 500);
        }
    }

    public function history(Request $request)
    {
        $request->validate([
            'phone' => ['nullable', 'string'],
            'client_id' => ['nullable', 'exists:clients,id'],
        ]);

        if (! $request->phone && ! $request->client_id) {
            return response()->json([
                'success' => false,
                'message' => 'Kërkohet telefoni ose klienti për të marrë historinë.',
            ], 422);
        }

        $query = Order::with(['items.product'])->latest();

        if ($request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->phone) {
            $query->where('phone', $request->phone);
        }

        // Get orders and remove duplicates by ID
        $orders = $query->limit(20)->get()->unique('id')->take(10)->values();

        // Only load clientLocation if the column exists and table exists
        if (\Illuminate\Support\Facades\Schema::hasTable('client_locations') &&
            \Illuminate\Support\Facades\Schema::hasColumn('orders', 'client_location_id')) {
            try {
                $orders->load('clientLocation');
            } catch (\Exception $e) {
                // If clientLocation relation fails, continue without it
                \Log::warning('Failed to load clientLocation relation: '.$e->getMessage());
            }
        }

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
            'data' => $order->load(['items.product', 'clientLocation']),
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
            'paid_at' => ['sometimes', 'nullable', 'date'],
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
            'paid_at',
        ]);

        // NEVER allow order_number to be changed - it must remain the same from creation
        if (isset($updateData['order_number'])) {
            unset($updateData['order_number']);
        }

        // Payment status: allow manual paid_at (e.g. paid later than invoice date)
        if ($request->has('is_paid')) {
            if (! $request->boolean('is_paid')) {
                $updateData['paid_at'] = null;
            } else {
                // If client provided a paid_at, keep it. Otherwise set on transition unpaid -> paid.
                if (! $request->has('paid_at') || $request->input('paid_at') === null || $request->input('paid_at') === '') {
                    if (! $order->is_paid) {
                        $updateData['paid_at'] = now();
                    } else {
                        unset($updateData['paid_at']); // keep existing paid_at
                    }
                }
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
            'data' => $order->load(['items.product', 'clientLocation']),
        ]);
    }

    public function sendOrderEmail(Request $request)
    {
        $request->validate([
            'order_data' => ['required', 'array'],
            'customer_data' => ['required', 'array'],
            'cart_items' => ['required', 'array'],
            'total_items' => ['required', 'integer', 'min:0'],
            'total_price' => ['nullable', 'numeric', 'min:0'],
            'invoice_html' => ['nullable', 'string'],
        ]);

        try {
            $orderData = $request->order_data;
            $customerData = $request->customer_data;
            $cartItems = $request->cart_items;
            $totalItems = $request->total_items;
            $totalPrice = $request->total_price ?? 0;
            $invoiceHtml = $request->input('invoice_html');

            $orderInbox = config('arontrade.order_inbox');

            if (empty($orderInbox) || ! is_string($orderInbox)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Emaili i porosive nuk është konfiguruar. Vendosni OFFICIAL_ORDER_EMAIL ose MAIL_ORDER_INBOX në .env.',
                ], 500);
            }

            $pdfBinary = null;
            $pdfFilename = null;
            if (is_string($invoiceHtml) && trim($invoiceHtml) !== '') {
                $orderNumber = $orderData['order_number'] ?? null;
                $safeOrderNumber = is_string($orderNumber) && $orderNumber !== ''
                    ? preg_replace('/[^A-Za-z0-9\-_]/', '-', $orderNumber)
                    : 'PA-NR';
                $pdfFilename = 'Fature-'.$safeOrderNumber.'.pdf';
                $pdfBinary = Pdf::loadHTML($invoiceHtml)->setPaper('a4')->output();
            }

            // Dërgo emailin (me PDF nëse u gjenerua)
            Mail::to($orderInbox)->send(
                new OrderConfirmationMail(
                    $orderData,
                    $customerData,
                    $cartItems,
                    $totalItems,
                    $totalPrice,
                    $pdfBinary,
                    $pdfFilename
                )
            );

            $mailer = (string) config('mail.default');
            $reachesInbox = ! in_array($mailer, ['log', 'array'], true);

            return response()->json([
                'success' => true,
                'message' => $reachesInbox
                    ? 'Porosia u dërgua në inbox.'
                    : 'Porosia u përpunua, por nuk u dërgua në Gmail (mënyra e postës është vetëm për regjistrim lokal).',
                'delivery' => [
                    'mode' => $mailer,
                    'reached_inbox' => $reachesInbox,
                    'recipient' => $orderInbox,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error sending order email: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gabim gjatë dërgimit të emailit: '.$e->getMessage(),
            ], 500);
        }
    }

    public function generateInvoicePdf(Request $request)
    {
        $request->validate([
            'order_number' => ['required', 'string', 'max:64'],
            'invoice_html' => ['required', 'string'],
        ]);

        try {
            $orderNumber = (string) $request->input('order_number');
            $invoiceHtml = (string) $request->input('invoice_html');

            $safeOrderNumber = preg_replace('/[^A-Za-z0-9\-_]/', '-', $orderNumber);
            $fileName = 'Fature-'.$safeOrderNumber.'.pdf';
            $relativePath = 'invoices/'.$fileName;

            $pdfBinary = Pdf::loadHTML($invoiceHtml)->setPaper('a4')->output();
            Storage::disk('public')->put($relativePath, $pdfBinary);

            $publicPath = Storage::disk('public')->url($relativePath); // zakonisht "/storage/..."
            $absoluteUrl = rtrim($request->getSchemeAndHttpHost(), '/').$publicPath;

            return response()->json([
                'success' => true,
                'data' => [
                    'order_number' => $orderNumber,
                    'pdf_path' => $publicPath,
                    'pdf_url' => $absoluteUrl,
                    'file_name' => $fileName,
                ],
            ]);
        } catch (\Throwable $e) {
            \Log::error('Invoice PDF generation failed', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => config('app.debug')
                    ? $e->getMessage()
                    : 'Nuk u arrit të krijohet PDF. Ju lutem provoni përsëri.',
            ], 500);
        }
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

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private static function filterToTableColumns(string $table, array $row): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        $allowed = array_flip(Schema::getColumnListing($table));

        return array_intersect_key($row, $allowed);
    }

    private function generateOrderNumber(): string
    {
        $datePart = now()->format('Ymd');
        $randomPart = Str::upper(Str::random(4));
        $count = Order::whereDate('created_at', now()->toDateString())->count() + 1;
        $countPart = str_pad((string) $count, 3, '0', STR_PAD_LEFT);

        return "GT-{$datePart}-{$countPart}{$randomPart}";
    }

    /**
     * Numër rezervë kur UNIQUE në DB përputhet me rresht të fshirë (soft delete)
     * ose me përplasje të rrallë — validimi Laravel nuk përputhet gjithmonë me indeksin e MySQL.
     */
    private function generateUniqueOrderNumberFallback(): string
    {
        return 'GT-'.now()->format('Ymd').'-'.strtoupper(Str::random(8));
    }

    private function isDuplicateOrderNumberConstraint(QueryException $e): bool
    {
        $message = $e->getMessage();
        $lower = strtolower($message);
        if (! str_contains($lower, 'order_number') && ! str_contains($lower, 'orders_order_number')) {
            return false;
        }

        $sqlState = $e->errorInfo[0] ?? '';
        $driverCode = $e->errorInfo[1] ?? null;

        if ($sqlState === '23505') {
            return true;
        }

        if ((int) $driverCode === 1062) {
            return true;
        }

        if ((int) $driverCode === 19 && str_contains($message, 'UNIQUE')) {
            return true;
        }

        return str_contains($message, 'Duplicate entry')
            || str_contains($message, 'UNIQUE constraint failed');
    }
}
