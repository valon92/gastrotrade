<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Make phone nullable and remove unique constraint
            $table->string('phone')->nullable()->change();
            
            // Make store_name required (not nullable) and add unique constraint
            $table->string('store_name')->nullable(false)->change();
            $table->unique('store_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Revert phone to required and unique
            $table->string('phone')->nullable(false)->unique()->change();
            
            // Revert store_name to nullable and remove unique
            $table->dropUnique(['store_name']);
            $table->string('store_name')->nullable()->change();
        });
    }
};

