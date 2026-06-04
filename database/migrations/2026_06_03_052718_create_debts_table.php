<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('discipline_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['DEBT', 'CLOSED'])->default('DEBT');
            $table->decimal('grade_value', 5, 2)->nullable();
            $table->string('grade_scale', 20)->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('debt_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')->constrained()->cascadeOnDelete();
            $table->string('old_status', 20)->nullable();
            $table->string('new_status', 20);
            $table->foreignId('changed_by_id')->constrained('users');
            $table->timestamp('changed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_status_logs');
        Schema::dropIfExists('debts');
    }
};