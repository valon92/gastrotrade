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
            $table->boolean('has_vat')->default(false)->after('total_amount');
            $table->decimal('vat_amount', 10, 2)->nullable()->after('has_vat');
            $table->decimal('amount_before_vat', 10, 2)->nullable()->after('vat_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['has_vat', 'vat_amount', 'amount_before_vat']);
        });
    }
};
