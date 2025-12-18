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
        Schema::create('user_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->string('elective_type'); // Polymorphic type (Awpf or Fwpm)
            $table->unsignedBigInteger('elective_choice_id'); // Polymorphic ID
            $table->unsignedBigInteger('parent_elective_choice_id')->nullable();
            $table->timestamps();

            // Self-referential foreign key for ordered choices
            $table->foreign('parent_elective_choice_id')
                ->references('id')
                ->on('user_selections')
                ->cascadeOnDelete();

            // Index for performance
            $table->index(['user_id', 'semester_id', 'elective_type']);
            $table->index(['elective_type', 'elective_choice_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_selections');
    }
};
