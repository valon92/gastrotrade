<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PipezaProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pipezaCategory = Category::where('slug', 'pipeza')->first();
        
        if (!$pipezaCategory) {
            $this->command->error('Category "Pipëza" not found!');
            return;
        }

        // Delete existing pipeza products
        Product::where('category_id', $pipezaCategory->id)->delete();

        // Define pipeza products with proper organization
        $pipezaProducts = [
            // 100Cp products (Kompleti 20Cp)
            [
                'category_id' => $pipezaCategory->id,
                'name' => 'Pipëza Transparente për Pije (100Cp - Kompleti 20Cp)',
                'slug' => 'pipeza-transparente-per-pije-100cp-kompleti-20cp',
                'description' => 'Pipëza transparente të qarta 100Cp për pije të ndryshme. Materiali i pastër dhe i sigurt për përdorim. Të qëndrueshme dhe të lehta. Ideal për kafetë, restorantet, evente dhe përdorim shtëpiak. Transparenca lejon shikimin e pijes dhe krijon një pamje elegante. Përshtatet për pije të nxehta dhe të ftohta.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6819.jpeg',
                'is_featured' => false,
                'sort_order' => 1,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $pipezaCategory->id,
                'name' => 'Pipëza Te Zi për Pije (100Cp - Kompleti 20Cp)',
                'slug' => 'pipeza-te-zi-per-pije-100cp-kompleti-20cp',
                'description' => 'Pipëza plastike të zeza 100Cp të cilësisë së lartë për pije të ndryshme. Të qëndrueshme, të sigurta për përdorim dhe të lehta. Përshtaten për pije të nxehta dhe të ftohta. Ideal për kafetë, restorantet, evente dhe përdorim shtëpiak. Dizajni elegant i zi i përshtatet me çdo ambient.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6822.jpeg',
                'is_featured' => false,
                'sort_order' => 2,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $pipezaCategory->id,
                'name' => 'Pipëza Color për Pije (100Cp - Kompleti 20Cp)',
                'slug' => 'pipeza-color-per-pije-100cp-kompleti-20cp',
                'description' => 'Pipëza me ngjyra të ndryshme 100Cp për pije. Dizajn i bukur dhe i gjallë që i përshtatet çdo ambienti. Të qëndrueshme, të sigurta për përdorim dhe të lehta. Ideal për evente, festa, restorantet dhe kafetë. Ngjyrat e ndryshme lejojnë organizimin dhe dallimin e pijeve të ndryshme. Përshtatet për pije të nxehta dhe të ftohta.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6816.jpeg',
                'is_featured' => false,
                'sort_order' => 3,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            // 200Cp products (Kompleti 20Cp)
            [
                'category_id' => $pipezaCategory->id,
                'name' => 'Pipëza Transparente për Pije (200Cp - Kompleti 20Cp)',
                'slug' => 'pipeza-transparente-per-pije-200cp-kompleti-20cp',
                'description' => 'Pipëza transparente të qarta 200Cp për pije të ndryshme. Materiali i pastër dhe i sigurt për përdorim. Të qëndrueshme dhe të lehta. Ideal për kafetë, restorantet, evente dhe përdorim shtëpiak. Transparenca lejon shikimin e pijes dhe krijon një pamje elegante. Përshtatet për pije të nxehta dhe të ftohta.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6819.jpeg',
                'is_featured' => false,
                'sort_order' => 4,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $pipezaCategory->id,
                'name' => 'Pipëza Te Zi për Pije (200Cp - Kompleti 20Cp)',
                'slug' => 'pipeza-te-zi-per-pije-200cp-kompleti-20cp',
                'description' => 'Pipëza plastike të zeza 200Cp të cilësisë së lartë për pije të ndryshme. Të qëndrueshme, të sigurta për përdorim dhe të lehta. Përshtaten për pije të nxehta dhe të ftohta. Ideal për kafetë, restorantet, evente dhe përdorim shtëpiak. Dizajni elegant i zi i përshtatet me çdo ambient.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6822.jpeg',
                'is_featured' => false,
                'sort_order' => 5,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $pipezaCategory->id,
                'name' => 'Pipëza Color për Pije (200Cp - Kompleti 20Cp)',
                'slug' => 'pipeza-color-per-pije-200cp-kompleti-20cp',
                'description' => 'Pipëza me ngjyra të ndryshme 200Cp për pije. Dizajn i bukur dhe i gjallë që i përshtatet çdo ambienti. Të qëndrueshme, të sigurta për përdorim dhe të lehta. Ideal për evente, festa, restorantet dhe kafetë. Ngjyrat e ndryshme lejojnë organizimin dhe dallimin e pijeve të ndryshme. Përshtatet për pije të nxehta dhe të ftohta.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6816.jpeg',
                'is_featured' => false,
                'sort_order' => 6,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            // 500Cp products (Kompleti 10Cp)
            [
                'category_id' => $pipezaCategory->id,
                'name' => 'Pipëza Te Zi për Pije (500Cp - Kompleti 10Cp)',
                'slug' => 'pipeza-te-zi-per-pije-500cp-kompleti-10cp',
                'description' => 'Pipëza plastike të zeza 500Cp të cilësisë së lartë për pije të mëdha. Të qëndrueshme, të sigurta për përdorim dhe të lehta. Përshtaten për pije të nxehta dhe të ftohta. Ideal për kafetë, restorantet, evente dhe përdorim shtëpiak. Dizajni elegant i zi i përshtatet me çdo ambient.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 10 copa.',
                'image_path' => '/images/IMG_6822.jpeg',
                'is_featured' => false,
                'sort_order' => 7,
                'sold_by_package' => true,
                'pieces_per_package' => 10,
            ],
            [
                'category_id' => $pipezaCategory->id,
                'name' => 'Pipëza Color për Pije (500Cp - Kompleti 10Cp)',
                'slug' => 'pipeza-color-per-pije-500cp-kompleti-10cp',
                'description' => 'Pipëza me ngjyra të ndryshme 500Cp për pije të mëdha. Dizajn i bukur dhe i gjallë që i përshtatet çdo ambienti. Të qëndrueshme, të sigurta për përdorim dhe të lehta. Ideal për evente, festa, restorantet dhe kafetë. Ngjyrat e ndryshme lejojnë organizimin dhe dallimin e pijeve të ndryshme. Përshtatet për pije të nxehta dhe të ftohta.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 10 copa.',
                'image_path' => '/images/IMG_6816.jpeg',
                'is_featured' => false,
                'sort_order' => 8,
                'sold_by_package' => true,
                'pieces_per_package' => 10,
            ],
        ];

        foreach ($pipezaProducts as $productData) {
            $product = Product::create($productData);
            
            // Create product image record
            ProductImage::create([
                'product_id' => $product->id,
                'file_name' => basename($productData['image_path']),
                'file_path' => $productData['image_path'],
                'is_featured' => true,
                'sort_order' => 1,
            ]);
        }

        $this->command->info('Pipeza products created successfully!');
    }
}

