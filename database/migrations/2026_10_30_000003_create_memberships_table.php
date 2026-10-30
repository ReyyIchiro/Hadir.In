<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20)->default('member');
            $table->foreignId('primary_location_id')->nullable()
                ->constrained('locations')->nullOnDelete();
            $table->foreignId('division_id')->nullable()
                ->constrained('divisions')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'role']);
            $table->index(['tenant_id', 'primary_location_id']);
            $table->index(['tenant_id', 'division_id']);
        });

        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('email', 190);
            $table->string('role', 20)->default('member');
            $table->foreignId('primary_location_id')->nullable()
                ->constrained('locations')->nullOnDelete();
            $table->foreignId('division_id')->nullable()
                ->constrained('divisions')->nullOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->foreignId('invited_by_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('accepted_by_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'email']);
            $table->index(['tenant_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('memberships');
    }
};
