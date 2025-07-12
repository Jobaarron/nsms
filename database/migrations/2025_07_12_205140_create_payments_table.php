<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('payment_type');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('reference_number')->nullable();
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
