<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['supplier', 'category']);

        if ($request->has('low_stock') && $request->low_stock) {
            $query->whereRaw('stock_quantity <= min_stock_level');
        }

        if ($request->has('out_of_stock') && $request->out_of_stock) {
            $query->where('stock_quantity', '<=', 0);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $products = $query->orderBy('name')->get();

        // Sync stock for all products before calculating (ensure accuracy)
        foreach ($products as $product) {
            $product->syncStockFromMovements();
        }

        // Reload products to get synced stock
        $products = $query->orderBy('name')->get();

        // Calculate total ordered quantity (in pieces) per product from order items
        // Count orders that are: "ruajtur", "konfirmuar", "dërguar", "kompletuar"
        // Do NOT count: "anuluar", deleted orders (deleted_at IS NOT NULL), or orders without client_id
        $totalOrdered = \App\Models\OrderItem::select('order_items.product_id', DB::raw('SUM(
            CASE 
                WHEN order_items.sold_by_package = 1 AND order_items.pieces_per_package IS NOT NULL 
                THEN order_items.quantity * order_items.pieces_per_package 
                ELSE order_items.quantity 
            END
        ) as total_ordered_pieces'))
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotNull('order_items.product_id')
            ->whereNotNull('orders.client_id') // Only count orders with a client
            ->whereIn('orders.status', ['ruajtur', 'konfirmuar', 'dërguar', 'kompletuar'])
            ->whereNull('orders.deleted_at')
            ->groupBy('order_items.product_id')
            ->pluck('total_ordered_pieces', 'product_id');

        // Calculate profit and attach shortage info for each product
        $products = $products->map(function ($product) use ($totalOrdered) {
            // Get real stock (calculated from movements)
            $realStock = $product->calculateRealStock();
            
            // Ensure stock_quantity is synced
            if ($product->stock_quantity !== $realStock) {
                $product->stock_quantity = $realStock;
                $product->saveQuietly(); // Save without triggering events
            }

            $product->profit_margin = $product->price && $product->cost_price 
                ? $product->price - $product->cost_price 
                : null;
            $product->profit_percentage = $product->price && $product->cost_price && $product->cost_price > 0
                ? (($product->price - $product->cost_price) / $product->cost_price) * 100
                : null;
            $product->total_stock_value = $realStock * ($product->cost_price ?? 0);

            // Calculate ordered quantity and remaining stock
            // Porosit = Totali i porosive nga klientët
            // Mbetja = Stoku aktual - Porositë (negative = mungesë, positive = tepricë)
            $totalOrderedPieces = (int) ($totalOrdered[$product->id] ?? 0);
            $currentStock = $realStock;
            
            // Calculate remaining stock (can be negative if shortage)
            $remainingStock = $currentStock - $totalOrderedPieces;
            
            // Store ordered quantity in pieces (always set, even if 0)
            $product->ordered_quantity_pieces = $totalOrderedPieces;
            
            // Store ordered quantity in packages if product is sold by package
            if ($product->sold_by_package && $product->pieces_per_package && $totalOrderedPieces > 0) {
                $product->ordered_quantity_packages = $totalOrderedPieces / $product->pieces_per_package;
            } else {
                $product->ordered_quantity_packages = 0;
            }
            
            // Store remaining stock in pieces (can be negative, always set)
            $product->remaining_stock_pieces = $remainingStock;
            
            // Store remaining stock in packages if product is sold by package (always set)
            if ($product->sold_by_package && $product->pieces_per_package) {
                $product->remaining_stock_packages = $remainingStock / $product->pieces_per_package;
            } else {
                $product->remaining_stock_packages = 0;
            }
            
            // Calculate shortage (only if negative remaining stock)
            $shortageCp = $remainingStock < 0 ? abs($remainingStock) : 0;
            $product->shortage_quantity = $shortageCp;

            // Also provide shortage in packages if product is sold by package
            if ($product->shortage_quantity > 0 && $product->sold_by_package && $product->pieces_per_package) {
                $product->shortage_packages = $product->shortage_quantity / $product->pieces_per_package;
            } else {
                $product->shortage_packages = null;
            }

            // Store stock in packages for display (always set)
            if ($product->sold_by_package && $product->pieces_per_package) {
                $product->stock_packages = $currentStock / $product->pieces_per_package;
            } else {
                $product->stock_packages = 0;
            }

            // Add real_stock attribute for frontend
            $product->real_stock = $realStock;

            return $product;
        });

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function movements(Request $request)
    {
        $query = StockMovement::with(['product', 'stockReceipt', 'order']);

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $movements,
        ]);
    }

    public function report(Request $request)
    {
        // Sync all products before generating report
        Product::chunk(100, function ($products) {
            foreach ($products as $product) {
                $product->syncStockFromMovements();
            }
        });

        $totalProducts = Product::count();
        $totalStockValue = Product::sum(DB::raw('stock_quantity * COALESCE(cost_price, 0)'));
        $lowStockProducts = Product::whereRaw('stock_quantity <= min_stock_level')->count();
        $outOfStockProducts = Product::where('stock_quantity', '<=', 0)->count();

        // Calculate total profit potential (if all stock is sold)
        $totalProfitPotential = Product::sum(DB::raw('stock_quantity * (COALESCE(price, 0) - COALESCE(cost_price, 0))'));

        return response()->json([
            'success' => true,
            'data' => [
                'total_products' => $totalProducts,
                'total_stock_value' => $totalStockValue,
                'low_stock_products' => $lowStockProducts,
                'out_of_stock_products' => $outOfStockProducts,
                'total_profit_potential' => $totalProfitPotential,
            ],
        ]);
    }
}
