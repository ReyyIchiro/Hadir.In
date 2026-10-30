<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('timezone', 64)->default('Asia/Jakarta');
            $table->string('status', 20)->default('pending');
            $table->string('plan', 30)->default('standard');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $table->time('work_start')->default('08:00:00');
            $table->time('work_end')->default('17:00:00');
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->unsignedSmallInteger('early_window_minutes')->default(60);
            $table->unsignedSmallInteger('grace_minutes')->default(10);
            $table->unsignedSmallInteger('checkout_late_minutes')->default(240);
            $table->unsignedSmallInteger('early_leave_minutes')->default(10);
            $table->boolean('allow_unscheduled_attendance')->default(false);
            $table->boolean('require_location')->default(true);
            $table->boolean('require_selfie')->default(true);
            $table->unsignedSmallInteger('geofence_buffer_meters')->default(50);
            $table->unsignedSmallInteger('low_accuracy_meters')->default(100);
            $table->unsignedSmallInteger('gps_timeout_seconds')->default(15);
            $table->unsignedSmallInteger('selfie_retention_days')->default(90);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
        Schema::dropIfExists('tenants');
    }
};
