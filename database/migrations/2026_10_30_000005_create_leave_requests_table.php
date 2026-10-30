<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'user_id', 'start_date']);
            $table->index(['tenant_id', 'start_date', 'end_date']);
        });

        Schema::create('attendance_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('work_date');
            $table->foreignId('schedule_id')->nullable()
                ->constrained('schedules')->nullOnDelete();
            $table->foreignId('location_id')->nullable()
                ->constrained('locations')->nullOnDelete();
            $table->foreignId('leave_request_id')->nullable()
                ->constrained('leave_requests')->nullOnDelete();
            $table->string('status', 20);
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->unsignedSmallInteger('late_minutes')->default(0);
            $table->unsignedSmallInteger('early_leave_minutes')->default(0);
            $table->json('flags')->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_corrected')->default(false);
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id', 'work_date']);
            $table->index(['tenant_id', 'work_date', 'status']);
            $table->index(['tenant_id', 'location_id']);
            $table->index(['tenant_id', 'user_id']);
            $table->index('leave_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_days');
        Schema::dropIfExists('leave_requests');
    }
};
