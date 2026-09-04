<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offre_abonnements', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->default('Premium');
            $table->unsignedInteger('montant')->default(1000); // en XAF
            $table->string('devise')->default('XAF');
            $table->unsignedInteger('duree_jours')->default(30);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offre_abonnements');
    }
};