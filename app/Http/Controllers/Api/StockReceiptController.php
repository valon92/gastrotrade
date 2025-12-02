<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockReceipt;
use App\Models\StockReceiptItem;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\SupplierInvoice;
use App\Models\SupplierInvoiceItem;
use App\Helpers\UnitConverter;
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
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.unit_type' => ['nullable', 'string', 'in:package,kg'],
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
                    'unit_type' => $item['unit_type'] ?? null,
                ]);

                // Update product stock
                $product = Product::find($item['product_id']);
                if (!$product) {
                    continue; // Skip if product not found
                }
                
                $stockBefore = $product->stock_quantity ?? 0;
                
                // Determine quantity to add to stock (always in pieces/cp)
                // Use UnitConverter to properly convert from any unit to pieces
                // IMPORTANT: If unit_type is not explicitly set, assume 'cp' (pieces)
                // Do NOT auto-infer 'package' as this causes incorrect conversions
                // The frontend should always send unit_type explicitly
                $unitType = $item['unit_type'] ?? 'cp';
                
                // Convert quantity to pieces using UnitConverter
                $quantityToAdd = UnitConverter::convert(
                    $item['quantity'],
                    $unitType,
                    'cp',
                    $product
                );
                
                $product->stock_quantity = ($product->stock_quantity ?? 0) + (int)round($quantityToAdd);
                
                // Update cost price if not set or if new cost is different
                // Cost price should always be per piece (cp) in the database
                if (!$product->cost_price || abs($product->cost_price - $item['unit_cost']) > 0.0001) {
                    // If unit_cost is per kg, convert to per piece
                    if ($unitType === 'kg') {
                        $isKeseMbeturinash = stripos($product->name, 'kese mbeturinash') !== false;
                        if ($isKeseMbeturinash) {
                            // Convert cost from per kg to per piece
                            $size = UnitConverter::extractSizeFromKeseMbeturinash($product->name);
                            $piecesPerKg = UnitConverter::getPiecesPerKgForKeseMbeturinash($size);
                            if ($piecesPerKg > 0) {
                                $product->cost_price = $item['unit_cost'] / $piecesPerKg;
                            } else {
                                $product->cost_price = $item['unit_cost'];
                            }
                        } else {
                            // For non-Kese Mbeturinash with kg, keep as is (shouldn't happen normally)
                            $product->cost_price = $item['unit_cost'];
                        }
                    } elseif ($unitType === 'package' && $product->sold_by_package && $product->pieces_per_package) {
                        // If unit_cost is per package, convert to per piece
                        $product->cost_price = $item['unit_cost'] / $product->pieces_per_package;
                    } else {
                        // If unit_cost is per piece (cp), use directly
                        $product->cost_price = $item['unit_cost'];
                    }
                }
                
                $product->save();

                // Create stock movement
                // Store original quantity and unit in notes for tracking
                $movementNotes = 'Pranim malli - ' . $receipt->receipt_number;
                
                // Format notes based on unit type
                if ($unitType === 'kg') {
                    $movementNotes .= ' (Pranuar: ' . number_format($item['quantity'], 2) . ' kg = ' . round($quantityToAdd) . ' copa)';
                } elseif ($unitType === 'package' && $product->sold_by_package && $product->pieces_per_package) {
                    $movementNotes .= ' (Pranuar: ' . number_format($item['quantity'], 2) . ' kompleti = ' . round($quantityToAdd) . ' copa)';
                } else {
                    $movementNotes .= ' (Pranuar: ' . number_format($item['quantity'], 2) . ' copa)';
                }
                
                StockMovement::create([
                    'product_id' => $item['product_id'],
                    'movement_type' => 'receipt',
                    'stock_receipt_id' => $receipt->id,
                    'quantity' => (int)round($quantityToAdd), // Store in pieces
                    'unit_cost' => $product->cost_price, // Store per piece cost
                    'total_cost' => $totalCost,
                    'stock_before' => $stockBefore,
                    'stock_after' => $product->stock_quantity,
                    'notes' => $movementNotes,
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
                    
                    // For products sold by package, convert quantity from pieces to packages and unit_cost from per piece to per package
                    $quantity = $receiptItem->quantity;
                    $unitPrice = $receiptItem->unit_cost;
                    
                    // Check if this is "Kese Mbeturinash" with kg unit
                    $isKeseMbeturinash = $product && stripos($product->name, 'kese mbeturinash') !== false;
                    
                    // Determine unit_type for invoice item
                    $invoiceUnitType = $receiptItem->unit_type; // Default to receipt's unit_type
                    
                    if ($isKeseMbeturinash && $receiptItem->unit_type === 'kg') {
                        // For "Kese Mbeturinash" with kg, quantity and unit_cost stay as is (in kg)
                        $quantity = $receiptItem->quantity;
                        $unitPrice = $receiptItem->unit_cost;
                        $invoiceUnitType = 'kg';
                    } elseif ($product && $product->sold_by_package && $product->pieces_per_package) {
                        // For products sold by package (including "Kese Mbeturinash" with package unit)
                        // Convert from pieces to packages
                        $quantity = $receiptItem->quantity / $product->pieces_per_package;
                        // Convert from per piece to per package
                        $unitPrice = $receiptItem->unit_cost * $product->pieces_per_package;
                        // Set unit_type to 'package' for invoice display
                        $invoiceUnitType = 'package';
                    } else {
                        // For products not sold by package, quantity and unit_cost stay as is
                        $quantity = $receiptItem->quantity;
                        $unitPrice = $receiptItem->unit_cost;
                        // If unit_type is not set, default to 'cp'
                        if (!$invoiceUnitType) {
                            $invoiceUnitType = 'cp';
                        }
                    }
                    
                    SupplierInvoiceItem::create([
                        'supplier_invoice_id' => $invoice->id,
                        'product_id' => $receiptItem->product_id,
                        'product_name' => $product ? $product->name : 'Produkt i panjohur',
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $receiptItem->total_cost, // Total stays the same
                        'notes' => $receiptItem->notes,
                        'unit_type' => $invoiceUnitType, // Store unit_type for display
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
