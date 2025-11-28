<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Client;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\StockReceipt;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'all');
        
        $trash = [];
        
        if ($type === 'all' || $type === 'orders') {
            $trash['orders'] = Order::onlyTrashed()
                ->with(['client'])
                ->orderBy('deleted_at', 'desc')
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'type' => 'order',
                        'name' => $order->order_number,
                        'description' => "Klienti: {$order->customer_name} - {$order->business_name}",
                        'amount' => $order->total_amount,
                        'deleted_at' => $order->deleted_at,
                    ];
                });
        }
        
        if ($type === 'all' || $type === 'clients') {
            $trash['clients'] = Client::onlyTrashed()
                ->orderBy('deleted_at', 'desc')
                ->get()
                ->map(function ($client) {
                    return [
                        'id' => $client->id,
                        'type' => 'client',
                        'name' => $client->store_name ?? $client->name,
                        'description' => "Nr. Fiskal: {$client->fiscal_number} - {$client->city}",
                        'amount' => null,
                        'deleted_at' => $client->deleted_at,
                    ];
                });
        }
        
        if ($type === 'all' || $type === 'products') {
            $trash['products'] = Product::onlyTrashed()
                ->with(['category'])
                ->orderBy('deleted_at', 'desc')
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'type' => 'product',
                        'name' => $product->name,
                        'description' => $product->category ? "Kategoria: {$product->category->name}" : '',
                        'amount' => $product->price,
                        'deleted_at' => $product->deleted_at,
                    ];
                });
        }
        
        if ($type === 'all' || $type === 'suppliers') {
            $trash['suppliers'] = Supplier::onlyTrashed()
                ->orderBy('deleted_at', 'desc')
                ->get()
                ->map(function ($supplier) {
                    return [
                        'id' => $supplier->id,
                        'type' => 'supplier',
                        'name' => $supplier->name,
                        'description' => $supplier->contact_person ? "Kontakti: {$supplier->contact_person}" : '',
                        'amount' => null,
                        'deleted_at' => $supplier->deleted_at,
                    ];
                });
        }
        
        if ($type === 'all' || $type === 'supplier_invoices') {
            $trash['supplier_invoices'] = SupplierInvoice::onlyTrashed()
                ->with(['supplier'])
                ->orderBy('deleted_at', 'desc')
                ->get()
                ->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'type' => 'supplier_invoice',
                        'name' => $invoice->invoice_number,
                        'description' => $invoice->supplier ? "Prodhuesi: {$invoice->supplier->name}" : '',
                        'amount' => $invoice->total_amount,
                        'deleted_at' => $invoice->deleted_at,
                    ];
                });
        }
        
        if ($type === 'all' || $type === 'stock_receipts') {
            $trash['stock_receipts'] = StockReceipt::onlyTrashed()
                ->with(['supplier'])
                ->orderBy('deleted_at', 'desc')
                ->get()
                ->map(function ($receipt) {
                    return [
                        'id' => $receipt->id,
                        'type' => 'stock_receipt',
                        'name' => $receipt->receipt_number,
                        'description' => $receipt->supplier ? "Prodhuesi: {$receipt->supplier->name}" : '',
                        'amount' => $receipt->total_amount,
                        'deleted_at' => $receipt->deleted_at,
                    ];
                });
        }
        
        // Flatten and combine all trash items
        $allTrash = collect();
        foreach ($trash as $items) {
            $allTrash = $allTrash->merge($items);
        }
        
        // Sort by deleted_at desc
        $allTrash = $allTrash->sortByDesc('deleted_at')->values();
        
        return response()->json([
            'success' => true,
            'data' => $allTrash,
            'counts' => [
                'orders' => count($trash['orders'] ?? []),
                'clients' => count($trash['clients'] ?? []),
                'products' => count($trash['products'] ?? []),
                'suppliers' => count($trash['suppliers'] ?? []),
                'supplier_invoices' => count($trash['supplier_invoices'] ?? []),
                'stock_receipts' => count($trash['stock_receipts'] ?? []),
            ],
        ]);
    }
    
    public function restore(Request $request, $type, $id)
    {
        try {
            $model = $this->getModel($type, $id);
            
            if (!$model) {
                return response()->json([
                    'success' => false,
                    'message' => 'Artikulli nuk u gjet në shportën e fshirjeve.',
                ], 404);
            }
            
            $model->restore();
            
            return response()->json([
                'success' => true,
                'message' => 'Artikulli u rikthye me sukses.',
                'data' => $model,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gabim në rikthimin e artikullit: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    public function forceDelete(Request $request, $type, $id)
    {
        try {
            $model = $this->getModel($type, $id);
            
            if (!$model) {
                return response()->json([
                    'success' => false,
                    'message' => 'Artikulli nuk u gjet në shportën e fshirjeve.',
                ], 404);
            }
            
            $model->forceDelete();
            
            return response()->json([
                'success' => true,
                'message' => 'Artikulli u fshi përfundimisht.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gabim në fshirjen e artikullit: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    private function getModel($type, $id)
    {
        return match ($type) {
            'order' => Order::onlyTrashed()->find($id),
            'client' => Client::onlyTrashed()->find($id),
            'product' => Product::onlyTrashed()->find($id),
            'supplier' => Supplier::onlyTrashed()->find($id),
            'supplier_invoice' => SupplierInvoice::onlyTrashed()->find($id),
            'stock_receipt' => StockReceipt::onlyTrashed()->find($id),
            default => null,
        };
    }
}
