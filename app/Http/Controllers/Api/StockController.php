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

        // Calculate profit for each product
        $products = $products->map(function ($product) {
            $product->profit_margin = $product->price && $product->cost_price 
                ? $product->price - $product->cost_price 
                : null;
            $product->profit_percentage = $product->price && $product->cost_price && $product->cost_price > 0
                ? (($product->price - $product->cost_price) / $product->cost_price) * 100
                : null;
            $product->total_stock_value = $product->stock_quantity * ($product->cost_price ?? 0);
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
