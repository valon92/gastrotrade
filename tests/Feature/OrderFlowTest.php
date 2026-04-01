<?php

namespace Tests\Feature;

use App\Mail\OrderConfirmationMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_store_order_with_minimal_payload(): void
    {
        $payload = [
            'customer' => [
                'name' => 'Test User',
                'business_name' => 'Test Store',
                'fiscal_number' => '12345678',
                'city' => 'Prishtina',
                'phone' => '044123456',
                'viber' => null,
            ],
            'items' => [
                [
                    'product_id' => null,
                    'name' => 'Produkt test',
                    'quantity' => 2,
                    'sold_by_package' => false,
                    'pieces_per_package' => null,
                    'unit_type' => 'cp',
                    'unit_price' => 10.50,
                    'total_price' => 21.00,
                    'discount_amount' => 0,
                    'discount_type' => null,
                    'discount_value' => 0,
                ],
            ],
            'totals' => [
                'total_items' => 2,
                'subtotal' => 21.00,
                'discount_amount' => 0,
                'discount_type' => null,
                'discount_value' => 0,
                'total_amount' => 21.00,
            ],
            'has_vat' => false,
            'vat_amount' => null,
            'amount_before_vat' => null,
            'is_paid' => false,
            'paid_at' => null,
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['id', 'order_number', 'items']]);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Test User',
        ]);
    }

    public function test_send_order_email_accepts_valid_payload(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/orders/send-email', [
            'order_data' => [
                'order_number' => 'GT-20260101-001ABCD',
                'created_at' => now()->toIso8601String(),
            ],
            'customer_data' => [
                'name' => 'A',
                'storeName' => 'B',
                'fiscalNumber' => 'C',
                'city' => 'D',
            ],
            'cart_items' => [
                ['name' => 'Item 1', 'quantity' => 1, 'price' => 5],
            ],
            'total_items' => 1,
            'total_price' => 5,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        Mail::assertSent(OrderConfirmationMail::class);
    }
}
