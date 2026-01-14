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
        Schema::table('research_projects', function (Blueprint $table) {
            $table->foreignId('creator_id')
                ->nullable()
                ->after('supervisor')
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('semester_id')
                ->nullable()
                ->after('end_date')
                ->constrained()
                ->cascadeOnDelete();

            $table->index(['semester_id', 'creator_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('research_projects', function (Blueprint $table) {
            $table->dropIndex(['semester_id', 'creator_id']);
            $table->dropForeign(['semester_id']);
            $table->dropForeign(['creator_id']);
            $table->dropColumn(['creator_id', 'semester_id']);
        });
    }
};
