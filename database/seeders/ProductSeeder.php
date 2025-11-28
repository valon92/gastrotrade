<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories
        $perzieresCategory = Category::where('slug', 'perzieres')->first();
        $pipezaCategory = Category::where('slug', 'pipeza')->first();
        $aluminCategory = Category::where('slug', 'alumin-flete')->first();
        $pecetaCategory = Category::where('slug', 'peceta-leter')->first();
        $laguraCategory = Category::where('slug', 'te-lagura')->first();
        $mbeturinaCategory = Category::where('slug', 'mbeturina')->first();
        $tjeraCategory = Category::where('slug', 'te-tjera')->first();

        $products = [
            [
                'category_id' => $perzieresCategory->id,
                'name' => 'Luga Kafe E Bardh 10cp Kompleti',
                'slug' => 'luga-kafe-e-bardh-10cp-kompleti',
                'description' => 'Luga kafe e bardhë profesionale 10cp për përzierjen e kafesë. Materiali i sigurt dhe i qëndrueshëm siguron përdorim të besueshëm. Ideal për kafetë, restorantet dhe përdorim shtëpiak. Dizajni elegant dhe praktik.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 10 copa.',
                'image_path' => '/images/lugakafe.jpeg',
                'is_featured' => false,
                'sort_order' => 0,
                'sold_by_package' => true,
                'pieces_per_package' => 10,
            ],
            [
                'category_id' => $pipezaCategory->id,
                'name' => 'Pipëza Plastike të Zeza',
                'slug' => 'pipeza-plastike-te-zeza',
                'description' => 'Pipëza plastike të zeza të cilësisë së lartë për pije të ndryshme. Të qëndrueshme, të sigurta për përdorim dhe të lehta. Përshtaten për pije të nxehta dhe të ftohta. Ideal për kafetë, restorantet, evente dhe përdorim shtëpiak. Dizajni elegant i zi i përshtatet me çdo ambient.

**Llojet e disponueshme:**
• 100cp - Për pije të vogla
• 200cp - Për pije të mesme  
• 500cp - Për pije të mëdha

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.

Të gjitha llojet janë të sigurta për kontakt me ushqim dhe të lehta për transport.',
                'image_path' => '/images/IMG_6822.jpeg',
                'is_featured' => true,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $aluminCategory->id,
                'name' => 'Folie Alumini 9 Micron',
                'slug' => 'folie-alumini-9-micron',
                'description' => 'Folie alumini profesionale me trashësi 9 micron për ruajtjen dhe mbrojtjen e ushqimit. Cilësi e lartë, rezistente ndaj temperaturës dhe të sigurta për kontakt me ushqim. Ideal për ruajtjen e ushqimit, përgatitjen e pjatave dhe përdorim profesional në kuzhinë. Siguron mbrojtje të plotë nga ajri dhe lagështia.',
                'image_path' => '/images/IMG_6814.jpeg',
                'is_featured' => true,
            ],
            [
                'category_id' => $pecetaCategory->id,
                'name' => 'Letër Kuzhine NUSH 2 Shtresa',
                'slug' => 'leter-kuzhine-nush-2-shtresa',
                'description' => 'Letër kuzhine NUSH me 2 shtresa, 100% celulozë të pastër. Absorbueshme, të buta dhe të qëndrueshme. Ideal për pastrimin e kuzhinës, tavolinave dhe sipërfaqeve të tjera. Materiali i pastër siguron siguri dhe higjienë të plotë. Përshtatet për përdorim profesional dhe shtëpiak.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 12 copa.',
                'image_path' => '/images/IMG_6813.jpeg',
                'is_featured' => true,
                'sold_by_package' => true,
                'pieces_per_package' => 12,
            ],
            [
                'category_id' => $laguraCategory->id,
                'name' => 'Baby Wipes Canéa Aqua Pure',
                'slug' => 'baby-wipes-canea-aqua-pure',
                'description' => 'Peceta të lagura Canéa Aqua Pure për fëmijë. Formula e butë dhe e sigurt për lëkurën e ndjeshme të fëmijëve. Përmbajnë ujë të pastër dhe përbërës natyralë. Ideal për pastrimin e fëmijëve, si dhe për përdorim në kuzhinë për pastrimin e duarve dhe sipërfaqeve. Paketimi praktik dhe i lehtë për transport.',
                'image_path' => '/images/IMG_6818.jpeg',
                'is_featured' => true,
            ],
            [
                'category_id' => $pipezaCategory->id,
                'name' => 'Pipëza Transparente për Pije',
                'slug' => 'pipeza-transparente-per-pije',
                'description' => 'Pipëza transparente të qarta për pije të ndryshme. Materiali i pastër dhe i sigurt për përdorim. Të qëndrueshme dhe të lehta. Ideal për kafetë, restorantet, evente dhe përdorim shtëpiak. Transparenca lejon shikimin e pijes dhe krijon një pamje elegante. Përshtatet për pije të nxehta dhe të ftohta.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6819.jpeg',
                'is_featured' => false,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            // Kese Mbeturinash - Produkte të shumta me renditje specifike
            [
                'category_id' => $mbeturinaCategory->id,
                'name' => 'Kese Mbeturinash 40L',
                'slug' => 'kese-mbeturinash-40l',
                'description' => 'Kese mbeturinash 40L të forta dhe të qëndrueshme për përdorim profesional dhe shtëpiak. Materiali i fortë siguron qëndrueshmëri dhe siguri. Ideal për përdorim të vogël në restorante, kafetë, evente dhe përdorim shtëpiak. Rezistente ndaj thyerjes dhe të sigurta për transport.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6815.jpeg',
                'is_featured' => true,
                'sort_order' => 1,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $mbeturinaCategory->id,
                'name' => 'Kese Mbeturinash 70L',
                'slug' => 'kese-mbeturinash-70l',
                'description' => 'Kese mbeturinash 70L të forta dhe të qëndrueshme për përdorim profesional dhe shtëpiak. Materiali i fortë siguron qëndrueshmëri dhe siguri. Ideal për përdorim të mesëm në restorante, kafetë, evente dhe përdorim profesional. Rezistente ndaj thyerjes dhe të sigurta për transport.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6815.jpeg',
                'is_featured' => true,
                'sort_order' => 2,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $mbeturinaCategory->id,
                'name' => 'Kese Mbeturinash 120L',
                'slug' => 'kese-mbeturinash-120l',
                'description' => 'Kese mbeturinash 120L të forta dhe të qëndrueshme për përdorim profesional dhe shtëpiak. Materiali i fortë siguron qëndrueshmëri dhe siguri. Ideal për përdorim të madh në restorante, kafetë, evente dhe përdorim profesional. Rezistente ndaj thyerjes dhe të sigurta për transport.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6815.jpeg',
                'is_featured' => true,
                'sort_order' => 3,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $mbeturinaCategory->id,
                'name' => 'Kese Mbeturinash 150L',
                'slug' => 'kese-mbeturinash-150l',
                'description' => 'Kese mbeturinash 150L të forta dhe të qëndrueshme për përdorim profesional dhe shtëpiak. Materiali i fortë siguron qëndrueshmëri dhe siguri. Ideal për përdorim profesional në restorante, kafetë, evente dhe përdorim komercial. Rezistente ndaj thyerjes dhe të sigurta për transport.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6815.jpeg',
                'is_featured' => true,
                'sort_order' => 4,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $mbeturinaCategory->id,
                'name' => 'Kese Mbeturinash 170L',
                'slug' => 'kese-mbeturinash-170l',
                'description' => 'Kese mbeturinash 170L të forta dhe të qëndrueshme për përdorim profesional dhe shtëpiak. Materiali i fortë siguron qëndrueshmëri dhe siguri. Ideal për përdorim intensiv në restorante, kafetë, evente dhe përdorim komercial. Rezistente ndaj thyerjes dhe të sigurta për transport.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6815.jpeg',
                'is_featured' => true,
                'sort_order' => 5,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $mbeturinaCategory->id,
                'name' => 'Kese Mbeturinash 200L',
                'slug' => 'kese-mbeturinash-200l',
                'description' => 'Kese mbeturinash 200L të forta dhe të qëndrueshme për përdorim profesional dhe shtëpiak. Materiali i fortë siguron qëndrueshmëri dhe siguri. Ideal për përdorim komercial në restorante, kafetë, evente dhe përdorim profesional. Rezistente ndaj thyerjes dhe të sigurta për transport.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6815.jpeg',
                'is_featured' => true,
                'sort_order' => 6,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $mbeturinaCategory->id,
                'name' => 'Kese Mbeturinash 240L',
                'slug' => 'kese-mbeturinash-240l',
                'description' => 'Kese mbeturinash 240L të forta dhe të qëndrueshme për përdorim profesional dhe shtëpiak. Materiali i fortë siguron qëndrueshmëri dhe siguri. Ideal për përdorim industrial në restorante, kafetë, evente dhe përdorim komercial. Rezistente ndaj thyerjes dhe të sigurta për transport.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6815.jpeg',
                'is_featured' => true,
                'sort_order' => 7,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $mbeturinaCategory->id,
                'name' => 'Kese Mbeturinash 270L',
                'slug' => 'kese-mbeturinash-270l',
                'description' => 'Kese mbeturinash 270L të forta dhe të qëndrueshme për përdorim profesional dhe shtëpiak. Materiali i fortë siguron qëndrueshmëri dhe siguri. Ideal për përdorim të gjerë në restorante, kafetë, evente dhe përdorim komercial. Rezistente ndaj thyerjes dhe të sigurta për transport.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6815.jpeg',
                'is_featured' => true,
                'sort_order' => 8,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $mbeturinaCategory->id,
                'name' => 'Kese Mbeturinash 300L',
                'slug' => 'kese-mbeturinash-300l',
                'description' => 'Kese mbeturinash 300L të forta dhe të qëndrueshme për përdorim profesional dhe shtëpiak. Materiali i fortë siguron qëndrueshmëri dhe siguri. Ideal për përdorim maksimal në restorante, kafetë, evente dhe përdorim komercial. Rezistente ndaj thyerjes dhe të sigurta për transport.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6815.jpeg',
                'is_featured' => true,
                'sort_order' => 9,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $mbeturinaCategory->id,
                'name' => 'Kese Mbeturinash 70L ME LIDHSE',
                'slug' => 'kese-mbeturinash-70l-me-lidhse',
                'description' => 'Kese mbeturinash 70L me lidhse të forta dhe të qëndrueshme për përdorim profesional dhe shtëpiak. Materiali i fortë siguron qëndrueshmëri dhe siguri. Lidhja e sigurt siguron mbyllje të plotë dhe mbrojtje nga lagështia. Ideal për përdorim të mesëm në restorante, kafetë, evente dhe përdorim profesional. Rezistente ndaj thyerjes dhe të sigurta për transport.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6815.jpeg',
                'is_featured' => true,
                'sort_order' => 10,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $mbeturinaCategory->id,
                'name' => 'Kese Mbeturinash 120L ME LIDHSE',
                'slug' => 'kese-mbeturinash-120l-me-lidhse',
                'description' => 'Kese mbeturinash 120L me lidhse të forta dhe të qëndrueshme për përdorim profesional dhe shtëpiak. Materiali i fortë siguron qëndrueshmëri dhe siguri. Lidhja e sigurt siguron mbyllje të plotë dhe mbrojtje nga lagështia. Ideal për përdorim të madh në restorante, kafetë, evente dhe përdorim profesional. Rezistente ndaj thyerjes dhe të sigurta për transport.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6815.jpeg',
                'is_featured' => true,
                'sort_order' => 11,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $mbeturinaCategory->id,
                'name' => 'Kese Mbeturinash 200L ME LIDHSE',
                'slug' => 'kese-mbeturinash-200l-me-lidhse',
                'description' => 'Kese mbeturinash 200L me lidhse të forta dhe të qëndrueshme për përdorim profesional dhe shtëpiak. Materiali i fortë siguron qëndrueshmëri dhe siguri. Lidhja e sigurt siguron mbyllje të plotë dhe mbrojtje nga lagështia. Ideal për përdorim komercial në restorante, kafetë, evente dhe përdorim profesional. Rezistente ndaj thyerjes dhe të sigurta për transport.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6815.jpeg',
                'is_featured' => true,
                'sort_order' => 12,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $mbeturinaCategory->id,
                'name' => 'Kese Mbeturinash 220L ME LIDHSE',
                'slug' => 'kese-mbeturinash-220l-me-lidhse',
                'description' => 'Kese mbeturinash 220L me lidhse të forta dhe të qëndrueshme për përdorim profesional dhe shtëpiak. Materiali i fortë siguron qëndrueshmëri dhe siguri. Lidhja e sigurt siguron mbyllje të plotë dhe mbrojtje nga lagështia. Ideal për përdorim komercial në restorante, kafetë, evente dhe përdorim profesional. Rezistente ndaj thyerjes dhe të sigurta për transport.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6815.jpeg',
                'is_featured' => true,
                'sort_order' => 13,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $pipezaCategory->id,
                'name' => 'Pipëza me Ngjyra të Ndryshme',
                'slug' => 'pipeza-me-ngjyra-te-ndryshme',
                'description' => 'Pipëza me ngjyra të ndryshme për pije. Dizajn i bukur dhe i gjallë që i përshtatet çdo ambienti. Të qëndrueshme, të sigurta për përdorim dhe të lehta. Ideal për evente, festa, restorantet dhe kafetë. Ngjyrat e ndryshme lejojnë organizimin dhe dallimin e pijeve të ndryshme. Përshtatet për pije të nxehta dhe të ftohta.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6816.jpeg',
                'is_featured' => false,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $tjeraCategory->id,
                'name' => 'Mbulesë Tavoline LDP',
                'slug' => 'mbulese-tavoline-ldp',
                'description' => 'Mbulesë tavoline LDP (Low Density Polyethylene) për mbrojtjen e tavolinave. Materiali i qëndrueshëm dhe i lehtë siguron mbrojtje të plotë nga pika, ndotja dhe dëmtimet. Ideal për restorantet, kafetë, evente dhe përdorim shtëpiak. Lehtë për pastrim dhe përdorim të përsëritur. Transparenca lejon shikimin e dizajnit të tavolinës.',
                'image_path' => '/images/IMG_6817.jpeg',
                'is_featured' => false,
            ],
            [
                'category_id' => $tjeraCategory->id,
                'name' => 'Gota Letre Per Kafe 2.5oz',
                'slug' => 'gota-leter-per-kafe-2-5oz',
                'description' => 'Gota letre profesionale 2.5oz për kafe dhe pije të ngrohta. Materiali me cilësi të lartë mban temperaturën dhe siguron përdorim të sigurt në kafene, restorante dhe evente. Ideal për përdorim profesional dhe shtëpiak. Të sigurta për kontakt me ushqim dhe rezistente ndaj rrjedhjeve.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 40 copa.',
                'image_path' => '/images/IMG_6821.jpeg',
                'is_featured' => true,
                'sort_order' => 0,
                'sold_by_package' => true,
                'pieces_per_package' => 40,
            ],
            [
                'category_id' => $tjeraCategory->id,
                'name' => 'Gota Letre Per Kafe 3oz',
                'slug' => 'gota-leter-per-kafe-3oz',
                'description' => 'Gota letre profesionale 3oz për kafe dhe pije të ngrohta. Materiali me cilësi të lartë mban temperaturën dhe siguron përdorim të sigurt në kafene, restorante dhe evente. Ideal për përdorim profesional dhe shtëpiak. Të sigurta për kontakt me ushqim dhe rezistente ndaj rrjedhjeve.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6821.jpeg',
                'is_featured' => true,
                'sort_order' => 0,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $tjeraCategory->id,
                'name' => 'Gota Letre Per Kafe 4oz',
                'slug' => 'gota-leter-per-kafe-4oz',
                'description' => 'Gota letre profesionale 4oz për kafe dhe pije të ngrohta. Materiali me cilësi të lartë mban temperaturën dhe siguron përdorim të sigurt në kafene, restorante dhe evente. Ideal për përdorim profesional dhe shtëpiak. Të sigurta për kontakt me ushqim dhe rezistente ndaj rrjedhjeve.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 40 copa.',
                'image_path' => '/images/IMG_6821.jpeg',
                'is_featured' => true,
                'sort_order' => 0,
                'sold_by_package' => true,
                'pieces_per_package' => 40,
            ],
            [
                'category_id' => $tjeraCategory->id,
                'name' => 'Gota Letre Per Kafe 7oz',
                'slug' => 'gota-leter-per-kafe-7oz',
                'description' => 'Gota letre profesionale 7oz për kafe dhe pije të ngrohta. Materiali me cilësi të lartë mban temperaturën dhe siguron përdorim të sigurt në kafene, restorante dhe evente. Ideal për përdorim profesional dhe shtëpiak. Të sigurta për kontakt me ushqim dhe rezistente ndaj rrjedhjeve.

**Paketimi:** Shitet vetëm në komplete. 1 kompleti = 20 copa.',
                'image_path' => '/images/IMG_6821.jpeg',
                'is_featured' => true,
                'sort_order' => 0,
                'sold_by_package' => true,
                'pieces_per_package' => 20,
            ],
            [
                'category_id' => $tjeraCategory->id,
                'name' => 'Produkt Shtesë 1',
                'slug' => 'produkt-shtese-1',
                'description' => 'Produkt shtesë i cilësisë së lartë për kuzhinë. Materiali i sigurt dhe i qëndrueshëm siguron përdorim të besueshëm. Ideal për përdorim profesional dhe shtëpiak. Dizajni praktik dhe funksional. Përshtatet për nevojat e ndryshme të kuzhinës dhe siguron cilësi të lartë në përdorim.',
                'image_path' => '/images/IMG_6823.jpeg',
                'is_featured' => false,
            ],
            [
                'category_id' => $tjeraCategory->id,
                'name' => 'Produkt Shtesë 2',
                'slug' => 'produkt-shtese-2',
                'description' => 'Produkt shtesë profesional për kuzhinë. Cilësi e lartë dhe qëndrueshmëri e provuar. Materiali i sigurt për kontakt me ushqim. Ideal për restorantet, kafetë dhe përdorim profesional. Dizajni modern dhe praktik. Siguron efikasitet dhe lehtësi në përdorim për çdo nevojë të kuzhinës.',
                'image_path' => '/images/IMG_6824.jpeg',
                'is_featured' => false,
            ],
            [
                'category_id' => $tjeraCategory->id,
                'name' => 'Produkt Shtesë 3',
                'slug' => 'produkt-shtese-3',
                'description' => 'Produkt shtesë i cilësisë së lartë për kuzhinë dhe biznes. Materiali i sigurt dhe i qëndrueshëm siguron përdorim të besueshëm. Ideal për përdorim profesional në restorante, kafetë, markete dhe përdorim shtëpiak. Dizajni praktik dhe funksional. Përshtatet për nevojat e ndryshme të kuzhinës dhe siguron cilësi të lartë në përdorim.',
                'image_path' => '/images/IMG_6825.jpeg',
                'is_featured' => false,
            ],
        ];

        foreach ($products as $productData) {
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
}
}