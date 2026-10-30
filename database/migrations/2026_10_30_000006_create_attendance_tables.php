<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_day_id')->nullable()
                ->constrained('attendance_days')->nullOnDelete();
            $table->string('type', 20);
            $table->string('status', 20);
            $table->date('work_date');
            $table->timestamp('occurred_at');
            $table->foreignId('location_id')->nullable()
                ->constrained('locations')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('accuracy_meters', 8, 2)->nullable();
            $table->unsignedInteger('distance_meters')->nullable();
            $table->string('selfie_path')->nullable();
            $table->string('reason', 80)->nullable();
            $table->json('flags')->nullable();
            $table->char('attempt_uuid', 36)->unique();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id', 'work_date']);
            $table->index(['tenant_id', 'occurred_at']);
            $table->index(['tenant_id', 'location_id']);
            $table->index('attendance_day_id');
        });

        Schema::create('attendance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('pending');
            $table->text('note')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index('attendance_event_id');
            $table->index(['tenant_id', 'user_id']);
        });

        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_day_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('pending');
            $table->json('original_values');
            $table->json('requested_values');
            $table->text('reason');
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index('attendance_day_id');
            $table->index(['tenant_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_corrections');
        Schema::dropIfExists('attendance_reviews');
        Schema::dropIfExists('attendance_events');
    }
};
