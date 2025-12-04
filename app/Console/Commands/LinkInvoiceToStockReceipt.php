<?php

namespace App\Console\Commands;

use App\Models\SupplierInvoice;
use App\Models\StockReceipt;
use App\Models\StockReceiptItem;
use App\Models\StockMovement;
use App\Models\Product;
use App\Helpers\UnitConverter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LinkInvoiceToStockReceipt extends Command
{
    protected $signature = 'invoice:link-receipt {invoice_number}';
    protected $description = 'Link a supplier invoice to a stock receipt (create receipt if needed)';

    public function handle()
    {
        $invoiceNumber = $this->argument('invoice_number');
        
        $invoice = SupplierInvoice::where('invoice_number', $invoiceNumber)->with(['items.product'])->first();
        
        if (!$invoice) {
            $this->error("Faktura '{$invoiceNumber}' nuk u gjet!");
            return 1;
        }
        
        if ($invoice->stock_receipt_id) {
            $this->info("Faktura tashmë është e lidhur me Stock Receipt ID: {$invoice->stock_receipt_id}");
            return 0;
        }
        
        $this->info("Duke krijuar pranim mallrash për fakturën '{$invoiceNumber}'...");
        
        return DB::transaction(function () use ($invoice) {
            // Calculate total amount and items from invoice items
            $totalAmount = 0;
            $totalItems = 0;
            
            foreach ($invoice->items as $item) {
                // Calculate cost from unit_price (assuming unit_price is per piece)
                $quantityInPieces = $this->convertToPieces($item);
                $unitCost = $item->unit_price; // Assuming unit_price is per piece
                $totalCost = $quantityInPieces * $unitCost;
                $totalAmount += $totalCost;
                $totalItems += $quantityInPieces;
            }
            
            // Create stock receipt
            $receipt = StockReceipt::create([
                'supplier_id' => $invoice->supplier_id,
                'receipt_date' => $invoice->invoice_date,
                'notes' => "Pranim i krijuar automatikisht nga faktura {$invoice->invoice_number}",
                'total_amount' => $totalAmount,
                'total_items' => $totalItems,
                'status' => 'completed',
            ]);
            
            $this->info("✅ Stock Receipt u krijua: {$receipt->receipt_number} (ID: {$receipt->id})");
            
            // Create receipt items and movements
            foreach ($invoice->items as $invoiceItem) {
                $product = $invoiceItem->product;
                
                if (!$product) {
                    $this->warn("⚠️ Produkti '{$invoiceItem->product_name}' nuk u gjet në databazë. Duke kaluar...");
                    continue;
                }
                
                // Convert quantity to pieces
                $quantityInPieces = $this->convertToPieces($invoiceItem);
                $unitCost = $invoiceItem->unit_price; // Assuming unit_price is per piece
                $totalCost = $quantityInPieces * $unitCost;
                
                // Create receipt item
                $receiptItem = StockReceiptItem::create([
                    'stock_receipt_id' => $receipt->id,
                    'product_id' => $product->id,
                    'quantity' => $quantityInPieces,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'unit_type' => $invoiceItem->unit_type ?? 'cp',
                    'notes' => "Krijuar nga faktura {$invoice->invoice_number}",
                ]);
                
                $this->info("  ✅ Receipt Item u krijua për '{$product->name}': {$quantityInPieces}cp");
                
                // Create stock movement
                $movement = StockMovement::create([
                    'product_id' => $product->id,
                    'stock_receipt_id' => $receipt->id,
                    'movement_type' => 'receipt',
                    'quantity' => $quantityInPieces,
                    'notes' => "Pranim nga faktura {$invoice->invoice_number}",
                ]);
                
                $this->info("  ✅ Stock Movement u krijua: +{$quantityInPieces}cp");
            }
            
            // Link invoice to receipt
            $invoice->stock_receipt_id = $receipt->id;
            $invoice->save();
            
            $this->info("✅ Faktura u lidh me Stock Receipt ID: {$receipt->id}");
            
            // Sync stock for all products
            $this->info("🔄 Duke sinkronizuar stokun...");
            foreach ($invoice->items as $invoiceItem) {
                if ($invoiceItem->product) {
                    $invoiceItem->product->syncStockFromMovements();
                    $this->info("  ✅ Stoku u sinkronizua për '{$invoiceItem->product->name}'");
                }
            }
            
            // Clear cache
            \Illuminate\Support\Facades\Cache::forget('valid_receipt_ids');
            
            $this->info("✅ Procesi u përfundua me sukses!");
            
            return 0;
        });
    }
    
    protected function convertToPieces($invoiceItem): int
    {
        $product = $invoiceItem->product;
        
        if (!$product) {
            // If product not found, assume quantity is already in pieces
            return (int) $invoiceItem->quantity;
        }
        
        $quantity = (float) $invoiceItem->quantity;
        $unitType = $invoiceItem->unit_type ?? 'cp';
        
        // If unit_type is 'package' and product is sold by package
        if ($unitType === 'package' && $product->sold_by_package && $product->pieces_per_package) {
            return (int) ($quantity * $product->pieces_per_package);
        }
        
        // If unit_type is 'cp' or not set, assume quantity is already in pieces
        if ($unitType === 'cp' || !$unitType) {
            return (int) $quantity;
        }
        
        // For other unit types, use UnitConverter
        try {
            return (int) UnitConverter::convert($quantity, $unitType, 'cp', $product);
        } catch (\Exception $e) {
            // Fallback: assume quantity is already in pieces
            return (int) $quantity;
        }
    }
}

