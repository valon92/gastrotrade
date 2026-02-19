<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class GotaLetreSpecsSeeder extends Seeder
{
    /**
     * Specifikat (size, barcode) për gota letre për kafe.
     * Size në oz. Barkod mund të ndryshohet nga admin.
     */
    public function run(): void
    {
        $specs = [
            'gota-leter-per-kafe-2-5oz' => ['size' => '2.5oz', 'barcode' => 'GT-GL-2.5oz'],
            'gota-leter-per-kafe-3oz'   => ['size' => '3oz',   'barcode' => 'GT-GL-3oz'],
            'gota-leter-per-kafe-4oz'   => ['size' => '4oz',   'barcode' => 'GT-GL-4oz'],
            'gota-leter-per-kafe-7oz'   => ['size' => '7oz',   'barcode' => 'GT-GL-7oz'],
        ];

        foreach ($specs as $slug => $data) {
            Product::where('slug', $slug)->update([
                'size' => $data['size'],
                'barcode' => $data['barcode'],
            ]);
        }
    }
}
