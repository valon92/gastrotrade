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
            $table->string('size', 50)->nullable()->after('description');   // p.sh. 65x115
            $table->string('liters', 20)->nullable()->after('size');         // p.sh. 170L
            $table->string('barcode', 100)->nullable()->after('liters');      // numri i barcodit
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['size', 'liters', 'barcode']);
        });
    }
};
