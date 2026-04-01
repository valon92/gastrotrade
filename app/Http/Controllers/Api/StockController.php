<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StockController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('products')) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $hasStockQuantity = Schema::hasColumn('products', 'stock_quantity');
        $hasMinStockLevel = Schema::hasColumn('products', 'min_stock_level');
        $hasSupplierId = Schema::hasColumn('products', 'supplier_id');
        $hasDescription = Schema::hasColumn('products', 'description');

        $query = Product::with(['supplier', 'category']);

        if (
            $hasStockQuantity
            && $hasMinStockLevel
            && $request->has('low_stock')
            && $request->low_stock
        ) {
            $query->whereRaw('stock_quantity <= min_stock_level');
        }

        if ($hasStockQuantity && $request->has('out_of_stock') && $request->out_of_stock) {
            $query->where('stock_quantity', '<=', 0);
        }

        if ($hasSupplierId && $request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->when($hasDescription, function ($q) use ($search) {
                        $q->orWhere('description', 'like', '%' . $search . '%');
                    });
            });
        }

        $products = $query->orderBy('name')->get();

        // Sync stock for all products before calculating (ensure accuracy)
        // Guard for production environments with partial migrations (missing stock tables/columns).
        if (Schema::hasTable('stock_movements') && $hasStockQuantity) {
            foreach ($products as $product) {
                try {
                    $product->syncStockFromMovements();
                } catch (\Throwable $e) {
                    \Log::warning('Stock sync failed for product', [
                        'product_id' => $product->id,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Reload products to get synced stock
        $products = $query->orderBy('name')->get();

        // Calculate total ordered quantity (in pieces) per product from order items
        // Count orders that are: "ruajtur", "konfirmuar", "dërguar", "kompletuar"
        // Do NOT count: "anuluar", deleted orders (deleted_at IS NOT NULL), or orders without client_id
        // IMPORTANT: 
        // - If unit_type = 'package' or sold_by_package = 1, quantity is in packages (needs to be multiplied by pieces_per_package)
        // - If unit_type = 'cp' or sold_by_package = 0, quantity is already in pieces (customer bought by pieces)
        // - If unit_type is NULL (old orders), use sold_by_package as fallback
        // Cart.vue now sends actual_pieces as quantity, sets sold_by_package = false, and unit_type = 'cp' when customer buys by pieces
        $totalOrdered = collect();
        if (Schema::hasTable('order_items') && Schema::hasTable('orders')) {
            $hasUnitType = Schema::hasColumn('order_items', 'unit_type');
            $hasSoldByPackage = Schema::hasColumn('order_items', 'sold_by_package');
            $hasPiecesPerPackage = Schema::hasColumn('order_items', 'pieces_per_package');

            $sumExpr = 'SUM(order_items.quantity)';
            if ($hasUnitType && $hasSoldByPackage && $hasPiecesPerPackage) {
                $sumExpr = "SUM(\n            CASE \n                WHEN (order_items.unit_type = 'package' OR (order_items.unit_type IS NULL AND order_items.sold_by_package = 1)) \n                     AND order_items.pieces_per_package IS NOT NULL \n                THEN order_items.quantity * order_items.pieces_per_package \n                ELSE order_items.quantity \n            END\n        )";
            }

            try {
                $totalOrdered = \App\Models\OrderItem::select('order_items.product_id', DB::raw("$sumExpr as total_ordered_pieces"))
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->whereNotNull('order_items.product_id')
                    ->when(Schema::hasColumn('orders', 'client_id'), fn($q) => $q->whereNotNull('orders.client_id'))
                    ->when(Schema::hasColumn('orders', 'status'), fn($q) => $q->whereIn('orders.status', ['ruajtur', 'konfirmuar', 'dërguar', 'kompletuar']))
                    ->when(Schema::hasColumn('orders', 'deleted_at'), fn($q) => $q->whereNull('orders.deleted_at'))
                    ->groupBy('order_items.product_id')
                    ->pluck('total_ordered_pieces', 'product_id');
            } catch (\Throwable $e) {
                \Log::warning('Stock totalOrdered query failed', ['message' => $e->getMessage()]);
                $totalOrdered = collect();
            }
        }

        // Calculate profit and attach shortage info for each product
        $products = $products->map(function ($product) use ($totalOrdered) {
            $realStock = $product->stock_quantity ?? 0;
            if (Schema::hasTable('stock_movements') && Schema::hasColumn('products', 'stock_quantity')) {
                try {
                    $realStock = $product->calculateRealStock();
                } catch (\Throwable $e) {
                    \Log::warning('Product calculateRealStock failed', [
                        'product_id' => $product->id,
                        'message' => $e->getMessage(),
                    ]);
                    $realStock = (int) ($product->stock_quantity ?? 0);
                }
            }
            
            // Ensure stock_quantity is synced
            if (Schema::hasColumn('products', 'stock_quantity') && $product->stock_quantity !== $realStock) {
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
            // IMPORTANT: This is just for display purposes - the actual calculation is always in pieces
            if ($product->sold_by_package && $product->pieces_per_package && $totalOrderedPieces > 0) {
                $product->ordered_quantity_packages = $totalOrderedPieces / $product->pieces_per_package;
            } else {
                $product->ordered_quantity_packages = 0;
            }
            
            // Debug: Log if ordered_quantity_packages is being calculated incorrectly
            // This helps identify if there are old orders stored incorrectly
            if ($product->sold_by_package && $product->pieces_per_package && $totalOrderedPieces > 0) {
                $calculatedPackages = $totalOrderedPieces / $product->pieces_per_package;
                // If calculated packages is >= 1 and is a whole number, it might be from an old order stored as packages
                // But we can't know for sure, so we rely on the frontend logic to display correctly
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
        if (!Schema::hasTable('stock_movements')) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

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
        if (!Schema::hasTable('products')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_products' => 0,
                    'total_stock_value' => 0,
                    'low_stock_products' => 0,
                    'out_of_stock_products' => 0,
                    'total_profit_potential' => 0,
                ],
            ]);
        }

        $hasStockQuantity = Schema::hasColumn('products', 'stock_quantity');
        $hasCostPrice = Schema::hasColumn('products', 'cost_price');
        $hasPrice = Schema::hasColumn('products', 'price');
        $hasMinStockLevel = Schema::hasColumn('products', 'min_stock_level');

        // Sync all products before generating report (only when tables/columns exist)
        if (Schema::hasTable('stock_movements') && $hasStockQuantity) {
            Product::chunk(100, function ($products) {
                foreach ($products as $product) {
                    try {
                        $product->syncStockFromMovements();
                    } catch (\Throwable $e) {
                        \Log::warning('Report stock sync failed', [
                            'product_id' => $product->id,
                            'message' => $e->getMessage(),
                        ]);
                    }
                }
            });
        }

        $totalProducts = Product::count();
        $totalStockValue = ($hasStockQuantity && $hasCostPrice)
            ? Product::sum(DB::raw('stock_quantity * COALESCE(cost_price, 0)'))
            : 0;
        $lowStockProducts = ($hasStockQuantity && $hasMinStockLevel)
            ? Product::whereRaw('stock_quantity <= min_stock_level')->count()
            : 0;
        $outOfStockProducts = ($hasStockQuantity)
            ? Product::where('stock_quantity', '<=', 0)->count()
            : 0;

        $totalProfitPotential = ($hasStockQuantity && $hasPrice && $hasCostPrice)
            ? Product::sum(DB::raw('stock_quantity * (COALESCE(price, 0) - COALESCE(cost_price, 0))'))
            : 0;

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
