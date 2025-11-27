<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class UpdateProductSortOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sortOrders = [
            // Kese Mbeturinash (pa lidhëse)
            'kese-mbeturinash-40l' => 1,
            'kese-mbeturinash-70l' => 2,
            'kese-mbeturinash-120l' => 3,
            'kese-mbeturinash-150l' => 4,
            'kese-mbeturinash-170l' => 5,
            'kese-mbeturinash-200l' => 6,
            'kese-mbeturinash-240l' => 7,
            'kese-mbeturinash-270l' => 8,
            'kese-mbeturinash-300l' => 9,

            // Kese Mbeturinash me lidhëse
            'kese-mbeturinash-70l-me-lidhse' => 10,
            'kese-mbeturinash-120l-me-lidhse' => 11,
            'kese-mbeturinash-200l-me-lidhse' => 12,
            'kese-mbeturinash-220l-me-lidhse' => 13,

            // Pipëza 100Cp
            'pipeza-transparente-per-pije-100cp-kompleti-20cp' => 14,
            'pipeza-te-zi-per-pije-100cp-kompleti-20cp' => 15,
            'pipeza-color-per-pije-100cp-kompleti-20cp' => 16,

            // Pipëza 200Cp
            'pipeza-transparente-per-pije-200cp-kompleti-20cp' => 17,
            'pipeza-te-zi-per-pije-200cp-kompleti-20cp' => 18,
            'pipeza-color-per-pije-200cp-kompleti-20cp' => 19,

            // Pipëza 500Cp
            'pipeza-te-zi-per-pije-500cp-kompleti-10cp' => 20,
            'pipeza-color-per-pije-500cp-kompleti-10cp' => 21,
        ];

        // Update sort order
        foreach ($sortOrders as $slug => $order) {
            Product::where('slug', $slug)->update(['sort_order' => $order]);
        }

        // Rename 300Cp pipeza products to 500Cp if they still exist
        $renameMap = [
            'pipeza-te-zi-per-pije-300cp-kompleti-10cp' => [
                'name' => 'Pipëza Te Zi për Pije (500Cp - Kompleti 10Cp)',
                'slug' => 'pipeza-te-zi-per-pije-500cp-kompleti-10cp',
                'pieces_per_package' => 10,
                'sort_order' => 20,
            ],
            'pipeza-color-per-pije-300cp-kompleti-10cp' => [
                'name' => 'Pipëza Color për Pije (500Cp - Kompleti 10Cp)',
                'slug' => 'pipeza-color-per-pije-500cp-kompleti-10cp',
                'pieces_per_package' => 10,
                'sort_order' => 21,
            ],
        ];

        foreach ($renameMap as $oldSlug => $data) {
            $product = Product::where('slug', $oldSlug)->first();
            if ($product) {
                $product->update($data);
            }
        }

        $this->command->info('Product sort orders updated successfully!');
    }
}


