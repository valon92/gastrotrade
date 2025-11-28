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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('category_id')->constrained()->onDelete('set null');
            $table->integer('stock_quantity')->default(0)->after('price');
            $table->decimal('cost_price', 10, 2)->nullable()->after('stock_quantity');
            $table->integer('min_stock_level')->default(0)->after('cost_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['supplier_id', 'stock_quantity', 'cost_price', 'min_stock_level']);
        });
    }
};
