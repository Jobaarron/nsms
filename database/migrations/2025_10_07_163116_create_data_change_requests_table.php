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
        Schema::create('data_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollee_id')->constrained('enrollees')->onDelete('cascade');
            $table->string('field_name'); // The field being requested to change
            $table->text('old_value')->nullable(); // Current value
            $table->text('new_value'); // Requested new value
            $table->text('reason')->nullable(); // Reason for the change
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable(); // Notes from registrar/admin
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null'); // Who processed the request
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['enrollee_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_change_requests');
    }
};
