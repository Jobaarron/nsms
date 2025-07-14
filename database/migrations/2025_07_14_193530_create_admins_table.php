<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('employee_id')->unique()->nullable();
            $table->string('department')->nullable();
            $table->string('position')->default('Administrator');
            $table->enum('admin_level', ['super_admin', 'admin', 'moderator'])->default('admin');
            $table->json('permissions')->nullable(); // Additional admin-specific permissions
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('admin_level');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
