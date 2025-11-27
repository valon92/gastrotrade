<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 12, 2)->nullable()->after('total_amount');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('subtotal');
            $table->string('discount_type')->nullable()->after('discount_amount'); // 'percentage' or 'fixed'
            $table->decimal('discount_value', 12, 2)->nullable()->after('discount_type'); // percentage value or fixed amount
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'discount_amount', 'discount_type', 'discount_value']);
        });
    }
};

