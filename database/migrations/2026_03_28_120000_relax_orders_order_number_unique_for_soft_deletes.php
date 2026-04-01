<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Porositë e fshira (soft delete) mbajnë të njëjtin order_number; UNIQUE në nivel DB
 * bllokonte numra të rinj dhe shkaktonte 500 Duplicate entry.
 * Unikaliteti për porosi aktive mbetet te validimi Rule::unique(...)->whereNull('deleted_at').
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['order_number']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->index('order_number');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['order_number']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->unique('order_number');
        });
    }
};
