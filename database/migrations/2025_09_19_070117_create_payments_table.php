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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();

            // Foreign key for the fee being paid (nullable for custom payments)
            $table->foreignId('fee_id')->nullable()->constrained('fees')->onDelete('set null');

            // Polymorphic relationship for the payer (Enrollee or Student)
            $table->morphs('payable');

            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->enum('payment_method', ['full', 'quarterly', 'monthly'])->default('full');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // Payment schedule fields
            $table->date('scheduled_date')->nullable();
            $table->string('period_name')->nullable();
            $table->decimal('amount_received', 10, 2)->nullable();
            
            // Cashier processing fields
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->text('cashier_notes')->nullable();
            $table->enum('confirmation_status', ['pending', 'confirmed', 'rejected'])->default('pending');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
