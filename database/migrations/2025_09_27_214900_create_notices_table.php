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
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            
            // Notice content
            $table->string('title');
            $table->text('message');
            $table->enum('priority', ['normal', 'high', 'urgent'])->default('normal');
            
            // Targeting
            $table->foreignId('enrollee_id')->nullable()->constrained('enrollees')->onDelete('cascade');
            $table->boolean('is_global')->default(false); // For notices sent to multiple enrollees
            $table->string('target_status')->nullable(); // To be delete
            $table->string('target_grade_level')->nullable(); // To be delete
            
            // Admin tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Read status
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            
            // Email integration
            $table->boolean('sent_via_email')->default(false); // To be delete
            $table->timestamp('email_sent_at')->nullable(); // To be delete
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['enrollee_id', 'is_read']);
            $table->index(['is_global', 'target_status']);
            $table->index(['priority', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
