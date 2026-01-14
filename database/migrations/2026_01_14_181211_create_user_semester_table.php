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
        Schema::create('user_semester', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('semester_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedInteger('semester_number')
                ->comment('Which semester number the student is in (1, 2, 3, etc.)');
            $table->boolean('is_current')
                ->default(false)
                ->comment('Indicates if this is the user\'s current active semester');
            $table->timestamps();

            $table->index(['user_id', 'is_current']);
            $table->index(['user_id', 'semester_id']);
            $table->unique(['user_id', 'semester_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_semester');
    }
};
