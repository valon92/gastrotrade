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
        Schema::table('supplier_invoice_items', function (Blueprint $table) {
            $table->string('unit_type')->nullable()->after('notes'); // 'package' or 'kg' for Kese Mbeturinash
            $table->decimal('quantity', 10, 2)->change(); // Change from integer to decimal to support kg
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_invoice_items', function (Blueprint $table) {
            $table->dropColumn('unit_type');
            $table->integer('quantity')->change();
        });
    }
};
