<?php

use App\Enums\Language;
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
        Schema::create('awpfs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('content')->nullable();

            $table->integer('credits')->default(5);
            $table->enum(Language::class);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('awpfs');
    }
};
