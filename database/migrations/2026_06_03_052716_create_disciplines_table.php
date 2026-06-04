<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disciplines', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->nullable();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('discipline_teachers', function (Blueprint $table) {
            $table->foreignId('discipline_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['discipline_id', 'teacher_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discipline_teachers');
        Schema::dropIfExists('disciplines');
    }
};