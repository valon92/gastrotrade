<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupplierInvoice;
use App\Models\SupplierInvoiceItem;
use App\Models\StockReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = SupplierInvoice::with(['supplier', 'stockReceipt', 'items.product']);

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('invoice_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'stock_receipt_id' => ['nullable', 'exists:stock_receipts,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'has_vat' => ['nullable', 'boolean'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated) {
            $subtotal = 0;

            foreach ($validated['items'] as $item) {
                $totalPrice = $item['quantity'] * $item['unit_price'];
                $subtotal += $totalPrice;
            }

            $vatRate = $validated['has_vat'] ? ($validated['vat_rate'] ?? 18.00) : 0;
            $vatAmount = $validated['has_vat'] ? ($subtotal * $vatRate / 100) : 0;
            $totalAmount = $subtotal + $vatAmount;

            $invoice = SupplierInvoice::create([
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
                'status' => 'pending',
            ]);

            foreach ($validated['items'] as $item) {
                $totalPrice = $item['quantity'] * $item['unit_price'];
                
                SupplierInvoiceItem::create([
                    'supplier_invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $invoice->load(['supplier', 'stockReceipt', 'items.product']),
            ], 201);
        });
    }

    public function show(SupplierInvoice $supplierInvoice)
    {
        return response()->json([
            'success' => true,
            'data' => $supplierInvoice->load(['supplier', 'stockReceipt', 'items.product']),
        ]);
    }

    public function update(Request $request, SupplierInvoice $supplierInvoice)
    {
        $validated = $request->validate([
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
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

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
            if (isset($validated['status']) && $validated['status'] === 'paid' && !$supplierInvoice->paid_date) {
                $validated['paid_date'] = $validated['paid_date'] ?? now()->toDateString();
            } elseif (isset($validated['status']) && $validated['status'] !== 'paid') {
                $validated['paid_date'] = null;
            }

            $supplierInvoice->update($validated);

            return response()->json([
                'success' => true,
                'data' => $supplierInvoice->load(['supplier', 'stockReceipt', 'items.product']),
            ]);
        });
    }

    public function destroy(SupplierInvoice $supplierInvoice)
    {
        // Allow deletion of cancelled invoices, but not paid ones
        if ($supplierInvoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Nuk mund të fshihet fatura e paguar.',
            ], 422);
        }

        // Delete invoice items first (cascade should handle this, but being explicit)
        $supplierInvoice->items()->delete();
        
        // Delete the invoice
        $supplierInvoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fatura u fshi me sukses.',
        ]);
    }
}

