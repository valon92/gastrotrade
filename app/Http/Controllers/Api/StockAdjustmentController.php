<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Helpers\UnitConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function adjust(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0'],
            'adjustment_type' => ['required', 'string', 'in:increase,decrease,set'],
            'notes' => ['nullable', 'string'],
            'original_quantity' => ['nullable', 'numeric'],
            'original_unit' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated, $product) {
            $stockBefore = $product->stock_quantity;
            
            // Convert quantity to pieces (base unit) if needed
            $originalQuantity = $validated['quantity'];
            $originalUnit = $validated['original_unit'] ?? 'cp';
            
            // Convert to pieces using UnitConverter
            $adjustmentQuantity = UnitConverter::convert(
                $originalQuantity,
                $originalUnit,
                'cp',
                $product
            );
            
            // Round to integer for stock quantity
            $adjustmentQuantity = (int)round($adjustmentQuantity);

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

            // Create stock movement with unit information
            $notes = $validated['notes'] ?? 'Rregullim manual i stokut';
            if (isset($validated['original_unit']) && isset($validated['original_quantity'])) {
                $unitLabel = $this->getUnitLabel($validated['original_unit']);
                $notes .= sprintf(' (Sasia origjinale: %s %s = %s copa)', 
                    $validated['original_quantity'], 
                    $unitLabel,
                    round($adjustmentQuantity)
                );
            }
            
            StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'adjustment',
                'quantity' => $movementQuantity,
                'stock_before' => $stockBefore,
                'stock_after' => $product->stock_quantity,
                'notes' => $notes,
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
                $adjustmentQuantity = $adjustment['quantity'] ?? 0;

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

                // Create stock movement with unit information
                $notes = $adjustment['notes'] ?? 'Rregullim manual i stokut';
                if (isset($adjustment['original_unit']) && isset($adjustment['original_quantity'])) {
                    $unitLabel = $this->getUnitLabel($adjustment['original_unit']);
                    $notes .= sprintf(' (Sasia origjinale: %s %s)', $adjustment['original_quantity'], $unitLabel);
                }
                
                StockMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'adjustment',
                    'quantity' => $movementQuantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $product->stock_quantity,
                    'notes' => $notes,
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
    
    /**
     * Get human-readable label for unit
     */
    private function getUnitLabel($unit)
    {
        $labels = [
            'cp' => 'Copa',
            'kg' => 'Kilogram',
            'g' => 'Gram',
            'L' => 'Liter',
            'ml' => 'Mililitër',
            'm' => 'Metër',
            'cm' => 'Centimetër',
            'pcs' => 'Pjesë',
        ];
        
        return $labels[$unit] ?? $unit;
    }
}
