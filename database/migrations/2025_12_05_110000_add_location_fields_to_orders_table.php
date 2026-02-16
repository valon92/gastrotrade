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
            $table->string('location_unit_name')->nullable()->after('client_location_id');
            $table->string('location_street_number')->nullable()->after('location_unit_name');
            $table->string('location_phone')->nullable()->after('location_street_number');
            $table->string('location_viber')->nullable()->after('location_phone');
            $table->string('location_city')->nullable()->after('location_viber');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'location_unit_name',
                'location_street_number',
                'location_phone',
                'location_viber',
                'location_city'
            ]);
        });
    }
};

