<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function adjust(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer'],
            'adjustment_type' => ['required', 'string', 'in:increase,decrease,set'],
            'notes' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated, $product) {
            $stockBefore = $product->stock_quantity;
            $adjustmentQuantity = $validated['quantity'];

            switch ($validated['adjustment_type']) {
                case 'increase':
                    $product->stock_quantity += $adjustmentQuantity;
                    $movementQuantity = $adjustmentQuantity;
                    break;
                case 'decrease':
                    $product->stock_quantity = max(0, $product->stock_quantity - $adjustmentQuantity);
                    $movementQuantity = -$adjustmentQuantity;
                    break;
                case 'set':
                    $movementQuantity = $adjustmentQuantity - $stockBefore;
                    $product->stock_quantity = max(0, $adjustmentQuantity);
                    break;
            }

            $product->save();

            // Create stock movement
            StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'adjustment',
                'quantity' => $movementQuantity,
                'stock_before' => $stockBefore,
                'stock_after' => $product->stock_quantity,
                'notes' => $validated['notes'] ?? 'Rregullim manual i stokut',
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'product' => $product->load('supplier', 'category'),
                    'stock_before' => $stockBefore,
                    'stock_after' => $product->stock_quantity,
                    'adjustment' => $movementQuantity,
                ],
            ]);
        });
    }

    public function bulkAdjust(Request $request)
    {
        $validated = $request->validate([
            'adjustments' => ['required', 'array', 'min:1'],
            'adjustments.*.product_id' => ['required', 'exists:products,id'],
            'adjustments.*.quantity' => ['required', 'integer'],
            'adjustments.*.adjustment_type' => ['required', 'string', 'in:increase,decrease,set'],
            'adjustments.*.notes' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated) {
            $results = [];

            foreach ($validated['adjustments'] as $adjustment) {
                $product = Product::find($adjustment['product_id']);
                $stockBefore = $product->stock_quantity;
                $adjustmentQuantity = $adjustment['quantity'];

                switch ($adjustment['adjustment_type']) {
                    case 'increase':
                        $product->stock_quantity += $adjustmentQuantity;
                        $movementQuantity = $adjustmentQuantity;
                        break;
                    case 'decrease':
                        $product->stock_quantity = max(0, $product->stock_quantity - $adjustmentQuantity);
                        $movementQuantity = -$adjustmentQuantity;
                        break;
                    case 'set':
                        $movementQuantity = $adjustmentQuantity - $stockBefore;
                        $product->stock_quantity = max(0, $adjustmentQuantity);
                        break;
                }

                $product->save();

                // Create stock movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'adjustment',
                    'quantity' => $movementQuantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $product->stock_quantity,
                    'notes' => $adjustment['notes'] ?? 'Rregullim manual i stokut',
                ]);

                $results[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'stock_before' => $stockBefore,
                    'stock_after' => $product->stock_quantity,
                    'adjustment' => $movementQuantity,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);
        });
    }
}
