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
        Schema::table('stock_receipt_items', function (Blueprint $table) {
            $table->string('unit_type')->nullable()->after('notes'); // 'package' or 'kg' for Kese Mbeturinash
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_receipt_items', function (Blueprint $table) {
            $table->dropColumn('unit_type');
        });
    }
};
