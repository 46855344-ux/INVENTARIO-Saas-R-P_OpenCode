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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->string('document_number', 50)->nullable();
            $table->date('ordered_at');
            $table->string('status', 20)->default('draft')->index();
            $table->decimal('total', 15, 2)->default(0);
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->string('reception_idempotency_key', 100)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'reception_idempotency_key']);
            $table->index(['company_id', 'status', 'ordered_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
