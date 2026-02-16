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
        Schema::create('client_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                ->constrained('clients')
                ->onDelete('cascade');
            $table->string('unit_name'); // Pika 1, Pika 2, Degë A, etj.
            $table->string('street_number')->nullable(); // Nr. e rrugës për këtë pikë
            $table->string('phone')->nullable(); // Numri i telefonit për këtë pikë
            $table->string('viber')->nullable(); // Viber për këtë pikë
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_locations');
    }
};

