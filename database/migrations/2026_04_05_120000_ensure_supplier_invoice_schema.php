<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotent: për servera ku migrimet e vjetra nuk janë ekzekutuar plotësisht
 * (p.sh. 500 në /api/supplier-invoices kur mungon deleted_at ose unit_type).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('supplier_invoices')) {
            if (! Schema::hasColumn('supplier_invoices', 'deleted_at')) {
                Schema::table('supplier_invoices', function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
            foreach (['deleted_reason' => 'text', 'restored_at' => 'timestamp', 'restored_reason' => 'text', 'deleted_by' => 'string'] as $col => $type) {
                if (! Schema::hasColumn('supplier_invoices', $col)) {
                    Schema::table('supplier_invoices', function (Blueprint $table) use ($col, $type) {
                        if ($type === 'text') {
                            $table->text($col)->nullable();
                        } elseif ($type === 'timestamp') {
                            $table->timestamp($col)->nullable();
                        } else {
                            $table->string($col)->nullable();
                        }
                    });
                }
            }
        }

        if (Schema::hasTable('supplier_invoice_items')) {
            if (! Schema::hasColumn('supplier_invoice_items', 'unit_type')) {
                Schema::table('supplier_invoice_items', function (Blueprint $table) {
                    $table->string('unit_type')->nullable()->after('notes');
                });
            }
            if (Schema::hasColumn('supplier_invoice_items', 'quantity')) {
                $driver = Schema::getConnection()->getDriverName();
                if ($driver === 'mysql') {
                    $row = DB::selectOne(
                        "SHOW COLUMNS FROM supplier_invoice_items WHERE Field = 'quantity'"
                    );
                    $type = strtolower((string) ($row->Type ?? ''));
                    if ($type !== '' && str_contains($type, 'int') && ! str_contains($type, 'decimal')) {
                        DB::statement(
                            'ALTER TABLE supplier_invoice_items MODIFY quantity DECIMAL(10,2) NOT NULL'
                        );
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // Jo destructive — lë gjendjen siç është
    }
};
