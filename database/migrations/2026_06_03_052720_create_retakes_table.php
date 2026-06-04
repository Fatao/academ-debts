<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retakes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discipline_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['REGULAR', 'COMMISSION'])->default('REGULAR');
            $table->string('building_number', 20);
            $table->string('room_number', 20);
            $table->dateTime('start_datetime');
            $table->unsignedInteger('duration_minutes');
            $table->enum('status', ['SCHEDULED', 'IN_PROGRESS', 'COMPLETED'])->default('SCHEDULED');
            $table->foreignId('created_by_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('retake_teachers', function (Blueprint $table) {
            $table->foreignId('retake_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['retake_id', 'teacher_id']);
        });

        Schema::create('retake_students', function (Blueprint $table) {
            $table->foreignId('retake_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->enum('result_status', ['NOT_TAKEN', 'PASSED', 'FAILED'])->default('NOT_TAKEN');
            $table->decimal('grade_value', 5, 2)->nullable();
            $table->string('grade_scale', 20)->nullable();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('updated_at')->nullable();
            $table->primary(['retake_id', 'student_id']);
        });

        Schema::create('retake_debts', function (Blueprint $table) {
            $table->foreignId('retake_id')->constrained()->cascadeOnDelete();
            $table->foreignId('debt_id')->constrained()->cascadeOnDelete();
            $table->primary(['retake_id', 'debt_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retake_debts');
        Schema::dropIfExists('retake_students');
        Schema::dropIfExists('retake_teachers');
        Schema::dropIfExists('retakes');
    }
};