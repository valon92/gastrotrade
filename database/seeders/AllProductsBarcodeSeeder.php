<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class AllProductsBarcodeSeeder extends Seeder
{
    /**
     * Vendos barcode për të gjithë produktet që nuk e kanë.
     * Format: GT-{slug me shkronja të mëdha, vizë në vend të hapësirës}.
     * Produktet që kanë tashmë barcode (p.sh. nga KeseMbeturinashSpecsSeeder, GotaLetreSpecsSeeder) nuk ndryshohen.
     */
    public function run(): void
    {
        Product::whereNull('barcode')
            ->orWhere('barcode', '')
            ->get()
            ->each(function (Product $product) {
                $barcode = 'GT-' . strtoupper($product->slug);
                $product->update(['barcode' => $barcode]);
            });
    }
}
