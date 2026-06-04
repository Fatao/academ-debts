<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retake_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retake_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by_id')->constrained('users');
            $table->string('new_building', 20)->nullable();
            $table->string('new_room', 20)->nullable();
            $table->dateTime('new_start_datetime')->nullable();
            $table->unsignedInteger('new_duration_minutes')->nullable();
            $table->text('comment')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->text('dean_comment')->nullable();
            $table->foreignId('reviewed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retake_change_requests');
    }
};