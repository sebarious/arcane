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
        Schema::table('invoices', function (Blueprint $table) {
            // if they don't exist yet, add; if they exist, ensure nullable/default
            if (! Schema::hasColumn('invoices', 'number')) {
                $table->string('number')->unique()->after('id');
            }
            if (! Schema::hasColumn('invoices', 'store_id')) {
                $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            }
            if (! Schema::hasColumn('invoices', 'batch_id')) {
                $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            }
            $table->integer('total_pence')->default(0)->nullable()->change();
            $table->integer('internal_cost_pence')->default(0)->nullable()->change();
            $table->integer('internal_margin_pence')->default(0)->nullable()->change();
            $table->integer('internal_margin_vat_pence')->default(0)->nullable()->change();
            $table->string('status')->default('draft')->change();
            $table->date('issued_on')->nullable()->change();
            $table->date('due_on')->nullable()->change();
            $table->timestamp('paid_at')->nullable()->change();
            if (! Schema::hasColumn('invoices', 'stripe_invoice_id')) {
                $table->string('stripe_invoice_id')->nullable()->index();
            }
            if (! Schema::hasColumn('invoices', 'pdf_path')) {
                $table->string('pdf_path')->nullable();
            }
        });
    }

    public function down(): void
    {
        // no-op for now
    }
};
