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
        Schema::table('fwpms', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->text('content')->nullable()->after('name');
            $table->integer('credits')->default(5)->after('content');
            $table->string('language')->after('credits');
            $table->string('exam_type')->after('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fwpms', function (Blueprint $table) {
            $table->dropColumn(['name', 'content', 'credits', 'language', 'exam_type']);
        });
    }
};
