<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class KeseMbeturinashSpecsSeeder extends Seeder
{
    /**
     * Specifikat (size, liters, barcode) për kese mbeturinash.
     * Size në cm (gjerësi x lartësi). Barkod mund të ndryshohet nga admin.
     */
    public function run(): void
    {
        $specs = [
            'kese-mbeturinash-40l'   => ['size' => '45x55',  'liters' => '40L',  'barcode' => 'GT-KM-40L'],
            'kese-mbeturinash-70l'   => ['size' => '55x75',  'liters' => '70L',  'barcode' => 'GT-KM-70L'],
            'kese-mbeturinash-120l'  => ['size' => '65x110', 'liters' => '120L', 'barcode' => 'GT-KM-120L'],
            'kese-mbeturinash-150l'  => ['size' => '70x115', 'liters' => '150L', 'barcode' => 'GT-KM-150L'],
            'kese-mbeturinash-170l'  => ['size' => '75x120', 'liters' => '170L', 'barcode' => 'GT-KM-170L'],
            'kese-mbeturinash-200l'  => ['size' => '80x125', 'liters' => '200L', 'barcode' => 'GT-KM-200L'],
            'kese-mbeturinash-240l'  => ['size' => '85x130', 'liters' => '240L', 'barcode' => 'GT-KM-240L'],
            'kese-mbeturinash-270l'  => ['size' => '90x130', 'liters' => '270L', 'barcode' => 'GT-KM-270L'],
            'kese-mbeturinash-300l'  => ['size' => '95x140', 'liters' => '300L', 'barcode' => 'GT-KM-300L'],
            'kese-mbeturinash-70l-me-lidhse'   => ['size' => '55x75',  'liters' => '70L',  'barcode' => 'GT-KM-70L-ML'],
            'kese-mbeturinash-120l-me-lidhse'  => ['size' => '65x110', 'liters' => '120L', 'barcode' => 'GT-KM-120L-ML'],
            'kese-mbeturinash-200l-me-lidhse'  => ['size' => '80x125', 'liters' => '200L', 'barcode' => 'GT-KM-200L-ML'],
            'kese-mbeturinash-220l-me-lidhse'  => ['size' => '82x128', 'liters' => '220L', 'barcode' => 'GT-KM-220L-ML'],
        ];

        foreach ($specs as $slug => $data) {
            Product::where('slug', $slug)->update([
                'size' => $data['size'],
                'liters' => $data['liters'],
                'barcode' => $data['barcode'],
            ]);
        }
    }
}
