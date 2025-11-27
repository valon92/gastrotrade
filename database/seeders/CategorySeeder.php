<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Pipëza',
                'slug' => 'pipeza',
                'description' => 'Pipëza plastike për pije të ndryshme'
            ],
            [
                'name' => 'Përzierës',
                'slug' => 'perzieres',
                'description' => 'Përzierës kafeje dhe shkopinj druri'
            ],
            [
                'name' => 'Alumin/Fletë',
                'slug' => 'alumin-flete',
                'description' => 'Folie alumini dhe fletë të tjera'
            ],
            [
                'name' => 'Peceta/Letër',
                'slug' => 'peceta-leter',
                'description' => 'Peceta kuzhine dhe letër të pastër'
            ],
            [
                'name' => 'Mbeturina',
                'slug' => 'mbeturina',
                'description' => 'Kese mbeturinash dhe kontejnerë'
            ],
            [
                'name' => 'Të Lagura',
                'slug' => 'te-lagura',
                'description' => 'Peceta të lagura dhe baby wipes'
            ],
            [
                'name' => 'Të Tjera',
                'slug' => 'te-tjera',
                'description' => 'Produkte të tjera për kuzhinë'
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
    }
}
}