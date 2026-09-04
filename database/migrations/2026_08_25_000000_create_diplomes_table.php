<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diplomes', function (Blueprint $table) {
            $table->id();
            $table->string('numeroDiplome');
            $table->string('typeDiplome');
            $table->string('institution')->nullable();
            $table->string('nomTitulaire');
            $table->date('dateNaissance')->nullable();
            $table->string('lieuNaissance')->nullable();
            $table->string('mention')->nullable();
            $table->string('serieOuFiliere')->nullable();
            $table->json('informationsComplementaires')->nullable();
            $table->date('dateObtention');
            $table->string('session')->nullable();
            $table->date('dateDelivrance')->nullable();
            $table->string('lieuDelivrance')->nullable();
            $table->string('matricule')->nullable();
            $table->string('etablissement');
            $table->string('centreExamen')->nullable();
            $table->string('fichier');
            $table->foreignId('client_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('institution_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diplomes');
    }
};
