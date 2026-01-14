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
            $table->string('title')->after('id');
            $table->text('description')->nullable()->after('title');
            $table->string('supervisor')->after('description');
            $table->integer('credits')->default(5)->after('supervisor');
            $table->date('start_date')->nullable()->after('credits');
            $table->date('end_date')->nullable()->after('start_date');
            $table->integer('max_students')->default(1)->after('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('research_projects', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'supervisor', 'credits', 'start_date', 'end_date', 'max_students']);
        });
    }
};
