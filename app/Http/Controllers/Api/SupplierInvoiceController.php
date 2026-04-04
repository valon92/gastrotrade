<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockReceipt;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\SupplierInvoiceItem;
use App\Support\SupportReference;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SupplierInvoiceController extends Controller
{
    /**
     * Kur kolona deleted_at mungon në DB (migrime të papërfunduara), SoftDeletes global scope thyen SQL-in.
     */
    private function supplierInvoiceBaseQuery()
    {
        if (! Schema::hasColumn('supplier_invoices', 'deleted_at')) {
            return SupplierInvoice::withoutGlobalScopes();
        }

        return SupplierInvoice::query();
    }

    /**
     * Ngarko marrëdhëniet për përgjigje API pa SoftDeletes scope në query të brendshëm.
     * Nëse deleted_at mungon në një tabelë (DB e papërditësuar) por modeli përdor SoftDeletes,
     * load() standard thyen SQL-in («Unknown column deleted_at»).
     */
    private function loadSupplierInvoiceApiRelations(SupplierInvoice $invoice): void
    {
        try {
            $invoice->load(['items']);
        } catch (\Throwable $e) {
            \Log::warning('SupplierInvoice API: load items failed', [
                'message' => $e->getMessage(),
                'invoice_id' => $invoice->id,
            ]);
        }

        try {
            $invoice->setRelation(
                'supplier',
                $invoice->supplier_id
                    ? Supplier::withoutGlobalScopes()->whereKey($invoice->supplier_id)->first()
                    : null
            );
        } catch (\Throwable $e) {
            \Log::warning('SupplierInvoice API: supplier relation failed', [
                'message' => $e->getMessage(),
                'invoice_id' => $invoice->id,
            ]);
            $invoice->setRelation('supplier', null);
        }

        try {
            $receipt = $invoice->stock_receipt_id
                ? StockReceipt::withoutGlobalScopes()->whereKey($invoice->stock_receipt_id)->first()
                : null;
            $invoice->setRelation('stockReceipt', $receipt);
            if ($receipt) {
                try {
                    $receipt->load('items');
                } catch (\Throwable $e2) {
                    \Log::warning('SupplierInvoice API: stockReceipt.items failed', [
                        'message' => $e2->getMessage(),
                        'invoice_id' => $invoice->id,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('SupplierInvoice API: stockReceipt relation failed', [
                'message' => $e->getMessage(),
                'invoice_id' => $invoice->id,
            ]);
            $invoice->setRelation('stockReceipt', null);
        }

        if (! $invoice->relationLoaded('items')) {
            return;
        }

        foreach ($invoice->items as $item) {
            if (! $item->product_id) {
                $item->setRelation('product', null);

                continue;
            }
            try {
                $item->setRelation(
                    'product',
                    Product::withoutGlobalScopes()->whereKey($item->product_id)->first()
                );
            } catch (\Throwable $e) {
                \Log::warning('SupplierInvoice API: product relation failed', [
                    'message' => $e->getMessage(),
                    'item_id' => $item->id,
                ]);
                $item->setRelation('product', null);
            }
        }
    }

    public function index(Request $request)
    {
        if (! Schema::hasTable('supplier_invoices')) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $query = $this->supplierInvoiceBaseQuery();

        try {
            $query->with(['supplier', 'stockReceipt', 'items.product']);
        } catch (\Throwable $e) {
            \Log::warning('SupplierInvoice index: eager load failed', ['message' => $e->getMessage()]);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('status')) {
            if (Schema::hasColumn('supplier_invoices', 'status')) {
                $query->where('status', $request->status);
            }
        }

        if ($request->has('date_from')) {
            if (Schema::hasColumn('supplier_invoices', 'invoice_date')) {
                $query->whereDate('invoice_date', '>=', $request->date_from);
            }
        }

        if ($request->has('date_to')) {
            if (Schema::hasColumn('supplier_invoices', 'invoice_date')) {
                $query->whereDate('invoice_date', '<=', $request->date_to);
            }
        }

        try {
            $invoices = $query
                ->when(Schema::hasColumn('supplier_invoices', 'invoice_date'), fn ($q) => $q->orderBy('invoice_date', 'desc'))
                ->when(Schema::hasColumn('supplier_invoices', 'created_at'), fn ($q) => $q->orderBy('created_at', 'desc'))
                ->get();
        } catch (\Throwable $e) {
            \Log::warning('SupplierInvoice index failed', ['message' => $e->getMessage()]);
            $invoices = [];
        }

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'stock_receipt_id' => $request->filled('stock_receipt_id') ? $request->input('stock_receipt_id') : null,
            'supplier_id' => $request->filled('supplier_id') ? $request->input('supplier_id') : null,
        ]);

        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'stock_receipt_id' => ['nullable', 'exists:stock_receipts,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'has_vat' => ['nullable', 'boolean'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['nullable', 'string', 'in:pending,paid,overdue,cancelled'],
            'paid_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $subtotal = 0;

                foreach ($validated['items'] as $item) {
                    $qty = (float) $item['quantity'];
                    $totalPrice = $qty * (float) $item['unit_price'];
                    $subtotal += $totalPrice;
                }

                $vatRate = $validated['has_vat'] ? ($validated['vat_rate'] ?? 18.00) : 0;
                $vatAmount = $validated['has_vat'] ? ($subtotal * $vatRate / 100) : 0;
                $totalAmount = $subtotal + $vatAmount;

                // Mos përdor Schema::getColumnListing() këtu: me «php artisan schema:cache» lista mund të jetë e vjetër
                // dhe të përfshijë kolona që nuk ekzistojnë në DB → SQL «Unknown column».
                $createPayload = [
                    'supplier_id' => $validated['supplier_id'],
                    'stock_receipt_id' => $validated['stock_receipt_id'] ?? null,
                    'invoice_date' => $validated['invoice_date'],
                    'due_date' => $validated['due_date'] ?? null,
                    'subtotal' => $subtotal,
                    'has_vat' => $validated['has_vat'] ?? false,
                    'vat_rate' => $vatRate,
                    'vat_amount' => $vatAmount,
                    'total_amount' => $totalAmount,
                    'notes' => $validated['notes'] ?? null,
                    'status' => $validated['status'] ?? 'pending',
                    'paid_date' => (isset($validated['status']) && $validated['status'] === 'paid')
                        ? ($validated['paid_date'] ?? now()->toDateString())
                        : null,
                ];

                $invoice = SupplierInvoice::create($createPayload);

                foreach ($validated['items'] as $item) {
                    $qty = (float) $item['quantity'];
                    $totalPrice = $qty * (float) $item['unit_price'];

                    $itemPayload = [
                        'supplier_invoice_id' => $invoice->id,
                        'product_id' => $item['product_id'] ?? null,
                        'product_name' => $item['product_name'],
                        'quantity' => $qty,
                        'unit_price' => $item['unit_price'],
                        'total_price' => $totalPrice,
                        'notes' => $item['notes'] ?? null,
                    ];
                    SupplierInvoiceItem::create($itemPayload);
                }

                // Jo supplierInvoiceBaseQuery(): e njëjta problematikë si generateInvoiceNumber (schema:cache vs kolona reale).
                $fresh = SupplierInvoice::withoutGlobalScopes()->whereKey($invoice->id)->first();
                $model = $fresh ?? $invoice;
                $this->loadSupplierInvoiceApiRelations($model);

                return response()->json([
                    'success' => true,
                    'data' => $model,
                ], 201);
            }, 3);
        } catch (QueryException $e) {
            $ref = SupportReference::report('SupplierInvoice store SQL', $e, [
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Të dhënat e faturës nuk u ruajtën në databazë. Provoni përsëri; nëse problemi vazhdon, jepni kodin e referencës mbështetjes.',
                'reference' => $ref,
                'detail' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        } catch (\Throwable $e) {
            $ref = SupportReference::report('SupplierInvoice store', $e);

            return response()->json([
                'success' => false,
                'message' => 'Ruajtja e faturës në databazë dështoi. Provoni përsëri; nëse vazhdon, jepni kodin e referencës.',
                'reference' => $ref,
                'detail' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function show(SupplierInvoice $supplierInvoice)
    {
        $this->loadSupplierInvoiceApiRelations($supplierInvoice);

        return response()->json([
            'success' => true,
            'data' => $supplierInvoice,
        ]);
    }

    public function update(Request $request, SupplierInvoice $supplierInvoice)
    {
        $validated = $request->validate([
            'stock_receipt_id' => ['nullable', 'exists:stock_receipts,id'],
            'invoice_date' => ['sometimes', 'date'],
            'due_date' => ['nullable', 'date'],
            'has_vat' => ['nullable', 'boolean'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['sometimes', 'string', 'in:pending,paid,overdue,cancelled'],
            'paid_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'exists:supplier_invoice_items,id'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_name' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.unit_type' => ['nullable', 'string'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        try {
            return DB::transaction(function () use ($validated, $supplierInvoice) {
                // Update items if provided
                if (isset($validated['items'])) {
                    $subtotal = 0;
                    $existingItemIds = collect($validated['items'])->pluck('id')->filter()->toArray();

                    // Delete items that are not in the new list
                    $supplierInvoice->items()->whereNotIn('id', $existingItemIds)->delete();

                    foreach ($validated['items'] as $itemData) {
                        $totalPrice = $itemData['quantity'] * $itemData['unit_price'];
                        $subtotal += $totalPrice;

                        if (isset($itemData['id']) && $itemData['id']) {
                            // Update existing item
                            $item = $supplierInvoice->items()->find($itemData['id']);
                            if ($item) {
                                $item->update([
                                    'product_id' => $itemData['product_id'] ?? $item->product_id,
                                    'product_name' => $itemData['product_name'],
                                    'quantity' => $itemData['quantity'],
                                    'unit_price' => $itemData['unit_price'],
                                    'total_price' => $totalPrice,
                                    'unit_type' => $itemData['unit_type'] ?? $item->unit_type,
                                    'notes' => $itemData['notes'] ?? null,
                                ]);
                            }
                        } else {
                            // Create new item
                            $supplierInvoice->items()->create([
                                'product_id' => $itemData['product_id'] ?? null,
                                'product_name' => $itemData['product_name'],
                                'quantity' => $itemData['quantity'],
                                'unit_price' => $itemData['unit_price'],
                                'total_price' => $totalPrice,
                                'unit_type' => $itemData['unit_type'] ?? null,
                                'notes' => $itemData['notes'] ?? null,
                            ]);
                        }
                    }

                    // Recalculate totals
                    $hasVat = $validated['has_vat'] ?? $supplierInvoice->has_vat;
                    $vatRate = $hasVat ? ($validated['vat_rate'] ?? $supplierInvoice->vat_rate ?? 18.00) : 0;
                    $vatAmount = $hasVat ? ($subtotal * $vatRate / 100) : 0;
                    $totalAmount = $subtotal + $vatAmount;

                    $validated['subtotal'] = $subtotal;
                    $validated['vat_rate'] = $vatRate;
                    $validated['vat_amount'] = $vatAmount;
                    $validated['total_amount'] = $totalAmount;
                } else {
                    // Just update VAT if changed
                    if (isset($validated['has_vat']) || isset($validated['vat_rate'])) {
                        $hasVat = $validated['has_vat'] ?? $supplierInvoice->has_vat;
                        $vatRate = $hasVat ? ($validated['vat_rate'] ?? $supplierInvoice->vat_rate ?? 18.00) : 0;
                        $subtotal = $supplierInvoice->subtotal;
                        $vatAmount = $hasVat ? ($subtotal * $vatRate / 100) : 0;
                        $totalAmount = $subtotal + $vatAmount;

                        $validated['vat_rate'] = $vatRate;
                        $validated['vat_amount'] = $vatAmount;
                        $validated['total_amount'] = $totalAmount;
                    }
                }

                // Update status and paid_date
                if (isset($validated['status']) && $validated['status'] === 'paid' && ! $supplierInvoice->paid_date) {
                    $validated['paid_date'] = $validated['paid_date'] ?? now()->toDateString();
                } elseif (isset($validated['status']) && $validated['status'] !== 'paid') {
                    $validated['paid_date'] = null;
                }

                $supplierInvoice->update($validated);

                // If stock_receipt_id was updated, clear stock cache and sync stock
                if (isset($validated['stock_receipt_id'])) {
                    \Illuminate\Support\Facades\Cache::forget('valid_receipt_ids');
                    // Sync stock for all products related to this receipt
                    if ($validated['stock_receipt_id']) {
                        $receipt = \App\Models\StockReceipt::find($validated['stock_receipt_id']);
                        if ($receipt) {
                            $productIds = $receipt->movements()->distinct()->pluck('product_id')->toArray();
                            foreach ($productIds as $productId) {
                                $product = \App\Models\Product::find($productId);
                                if ($product) {
                                    $product->syncStockFromMovements();
                                }
                            }
                        }
                    }
                }

                $this->loadSupplierInvoiceApiRelations($supplierInvoice);

                return response()->json([
                    'success' => true,
                    'data' => $supplierInvoice,
                ]);
            }, 3);
        } catch (QueryException $e) {
            $ref = SupportReference::report('SupplierInvoice update SQL', $e, [
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Përditësimi i faturës në databazë dështoi. Provoni përsëri; nëse vazhdon, jepni kodin e referencës.',
                'reference' => $ref,
                'detail' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        } catch (\Throwable $e) {
            $ref = SupportReference::report('SupplierInvoice update', $e);

            return response()->json([
                'success' => false,
                'message' => 'Ruajtja e ndryshimeve të faturës dështoi. Provoni përsëri; nëse vazhdon, jepni kodin e referencës.',
                'reference' => $ref,
                'detail' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy(Request $request, SupplierInvoice $supplierInvoice)
    {
        // Allow deletion of cancelled invoices, but not paid ones
        if ($supplierInvoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Nuk mund të fshihet fatura e paguar.',
            ], 422);
        }

        // Get deletion reason from request
        $deletedReason = $request->input('deleted_reason', 'Nuk u specifikua arsyeja');
        $deletedBy = $request->input('deleted_by', 'Sistem');

        // Delete invoice items first (cascade should handle this, but being explicit)
        $supplierInvoice->items()->delete();

        // Update invoice with deletion info before soft delete
        $supplierInvoice->deleted_reason = $deletedReason;
        $supplierInvoice->deleted_by = $deletedBy;
        $supplierInvoice->save();

        // Delete the invoice (soft delete)
        $supplierInvoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fatura u fshi me sukses.',
        ]);
    }
}
