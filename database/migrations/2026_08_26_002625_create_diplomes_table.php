<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// TABLE : diplomes
// ------------------------------------------------------------
// Correspond à la classe "Diplomes" du diagramme de classes.
// Un diplôme est SOUMIS par un Client OU DÉLIVRÉ par une
// Institution — d'où les deux colonnes de clé étrangère
// client_id et institution_id, toutes deux nullable (un
// diplôme n'a qu'UN SEUL des deux renseigné, jamais les deux).
// ============================================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diplomes', function (Blueprint $table) {
            // idDiplome : Laravel crée automatiquement une clé
            // primaire auto-incrémentée nommée "id" par $table->id().
            // On la garde ainsi (standard Laravel), les autres
            // attributs reprennent exactement les noms du diagramme.
            $table->id();

            $table->string('numeroDiplome');
            $table->string('typeDiplome');
            $table->string('nomTitulaire');
            $table->string('mention')->nullable();
            $table->date('dateObtention');
            $table->string('etablissement');
            $table->string('fichier'); // chemin/nom du fichier image du diplôme

            // --------------------------------------------------
            // Relations du diagramme :
            //   Client "1..1" --- soumet ---> "0..*" Diplomes
            //   Institution "1..1" --- délivre ---> "0..*" Diplomes
            // --------------------------------------------------
            // Qui a SOUMIS ce diplôme pour vérification (un Client).
            $table->foreignId('client_id')
                  ->nullable()
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Quelle Institution a DÉLIVRÉ ce diplôme (si connue).
            $table->foreignId('institution_id')
                  ->nullable()
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diplomes');
    }
};