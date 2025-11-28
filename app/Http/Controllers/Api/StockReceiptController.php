<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockReceipt;
use App\Models\StockReceiptItem;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\SupplierInvoice;
use App\Models\SupplierInvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = StockReceipt::with(['supplier', 'items.product']);

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('receipt_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('receipt_date', '<=', $request->date_to);
        }

        $receipts = $query->orderBy('receipt_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $receipts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'receipt_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
            'create_invoice' => ['nullable', 'boolean'],
            'invoice_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'has_vat' => ['nullable', 'boolean'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        return DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            $totalItems = 0;

            foreach ($validated['items'] as $item) {
                $totalCost = $item['quantity'] * $item['unit_cost'];
                $totalAmount += $totalCost;
                $totalItems += $item['quantity'];
            }

            $receipt = StockReceipt::create([
                'supplier_id' => $validated['supplier_id'],
                'receipt_date' => $validated['receipt_date'],
                'notes' => $validated['notes'] ?? null,
                'total_amount' => $totalAmount,
                'total_items' => $totalItems,
                'status' => 'completed',
            ]);

            foreach ($validated['items'] as $item) {
                $totalCost = $item['quantity'] * $item['unit_cost'];
                
                $receiptItem = StockReceiptItem::create([
                    'stock_receipt_id' => $receipt->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $totalCost,
                    'notes' => $item['notes'] ?? null,
                ]);

                // Update product stock
                $product = Product::find($item['product_id']);
                $stockBefore = $product->stock_quantity;
                $product->stock_quantity += $item['quantity'];
                
                // Update cost price if not set or if new cost is different
                if (!$product->cost_price || $product->cost_price != $item['unit_cost']) {
                    $product->cost_price = $item['unit_cost'];
                }
                
                $product->save();

                // Create stock movement
                StockMovement::create([
                    'product_id' => $item['product_id'],
                    'movement_type' => 'receipt',
                    'stock_receipt_id' => $receipt->id,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $totalCost,
                    'stock_before' => $stockBefore,
                    'stock_after' => $product->stock_quantity,
                    'notes' => 'Pranim malli - ' . $receipt->receipt_number,
                ]);
            }

            // Create supplier invoice if requested
            $invoice = null;
            if ($validated['create_invoice'] ?? false) {
                $subtotal = $totalAmount;
                $vatRate = ($validated['has_vat'] ?? false) ? ($validated['vat_rate'] ?? 18.00) : 0;
                $vatAmount = ($validated['has_vat'] ?? false) ? ($subtotal * $vatRate / 100) : 0;
                $invoiceTotal = $subtotal + $vatAmount;

                $invoice = SupplierInvoice::create([
                    'supplier_id' => $receipt->supplier_id,
                    'stock_receipt_id' => $receipt->id,
                    'invoice_date' => $validated['invoice_date'] ?? $receipt->receipt_date,
                    'due_date' => $validated['due_date'] ?? null,
                    'subtotal' => $subtotal,
                    'has_vat' => $validated['has_vat'] ?? false,
                    'vat_rate' => $vatRate,
                    'vat_amount' => $vatAmount,
                    'total_amount' => $invoiceTotal,
                    'status' => 'pending',
                    'notes' => 'Faturë e krijuar automatikisht nga pranimi i mallit #' . $receipt->receipt_number,
                ]);

                // Create invoice items from receipt items
                foreach ($receipt->items as $receiptItem) {
                    $product = Product::find($receiptItem->product_id);
                    SupplierInvoiceItem::create([
                        'supplier_invoice_id' => $invoice->id,
                        'product_id' => $receiptItem->product_id,
                        'product_name' => $product ? $product->name : 'Produkt i panjohur',
                        'quantity' => $receiptItem->quantity,
                        'unit_price' => $receiptItem->unit_cost,
                        'total_price' => $receiptItem->total_cost,
                        'notes' => $receiptItem->notes,
                    ]);
                }
            }

            $receipt->load(['supplier', 'items.product', 'supplierInvoice']);

            return response()->json([
                'success' => true,
                'data' => [
                    'receipt' => $receipt,
                    'invoice' => $invoice ? $invoice->load(['supplier', 'items.product']) : null,
                ],
            ], 201);
        });
    }

    public function show(StockReceipt $stockReceipt)
    {
        return response()->json([
            'success' => true,
            'data' => $stockReceipt->load(['supplier', 'items.product', 'movements']),
        ]);
    }

    public function update(Request $request, StockReceipt $stockReceipt)
    {
        // Only allow updating notes and status for completed receipts
        if ($stockReceipt->status === 'completed') {
            $validated = $request->validate([
                'notes' => ['nullable', 'string'],
                'status' => ['sometimes', 'string', 'in:pending,completed,cancelled'],
            ]);

            $stockReceipt->update($validated);

            return response()->json([
                'success' => true,
                'data' => $stockReceipt->load(['supplier', 'items.product']),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Pranimi i mallit nuk mund të modifikohet pasi është kompletuar.',
        ], 422);
    }

    public function destroy(StockReceipt $stockReceipt)
    {
        if ($stockReceipt->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Nuk mund të fshihet pranimi i kompletuar. Anulojeni nëse është e nevojshme.',
            ], 422);
        }

        $stockReceipt->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pranimi u fshi me sukses.',
        ]);
    }
}
