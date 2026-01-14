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
        Schema::table('user_selections', function (Blueprint $table) {
            $table->string('status')
                ->default('pending')
                ->after('parent_elective_choice_id');

            $table->string('enrollment_type')
                ->default('priority')
                ->after('status');

            $table->index(['status', 'semester_id']);
            $table->index(['elective_type', 'elective_choice_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_selections', function (Blueprint $table) {
            $table->dropIndex(['status', 'semester_id']);
            $table->dropIndex(['elective_type', 'elective_choice_id', 'status']);
            $table->dropColumn(['status', 'enrollment_type']);
        });
    }
};
