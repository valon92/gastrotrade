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
        Schema::table('supplier_invoices', function (Blueprint $table) {
            $table->text('deleted_reason')->nullable()->after('deleted_at');
            $table->timestamp('restored_at')->nullable()->after('deleted_reason');
            $table->text('restored_reason')->nullable()->after('restored_at');
            $table->string('deleted_by')->nullable()->after('restored_reason'); // User email or name
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_invoices', function (Blueprint $table) {
            $table->dropColumn(['deleted_reason', 'restored_at', 'restored_reason', 'deleted_by']);
        });
    }
};
