<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regles_verification', function (Blueprint $table) {
            $table->id();
            $table->string('institution');
            $table->string('typeDiplome');
            $table->json('champsObligatoires');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regles_verification');
    }
};