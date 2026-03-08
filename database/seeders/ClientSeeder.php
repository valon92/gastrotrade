<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Klientë test për kyçje në faqe (email + fjalëkalim).
     * Fjalëkalimi hashet automatikisht nga mutatori në Client.
     */
    public function run(): void
    {
        $clients = [
            [
                'name' => 'Valon Test',
                'email' => 'svalon95@gmail.com',
                'password' => 'Valon123',
                'store_name' => 'Dyqani Valon',
                'is_active' => true,
            ],
            [
                'name' => 'Klient Test 1',
                'email' => 'klient1@test.com',
                'password' => 'password',
                'store_name' => 'Biznesi 1',
                'is_active' => true,
            ],
            [
                'name' => 'Klient Test 2',
                'email' => 'klient2@test.com',
                'password' => 'password',
                'store_name' => 'Biznesi 2',
                'is_active' => true,
            ],
        ];

        foreach ($clients as $data) {
            Client::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'store_name' => $data['store_name'] ?? null,
                    'password' => $data['password'],
                    'is_active' => $data['is_active'] ?? true,
                ]
            );
        }
    }
}
