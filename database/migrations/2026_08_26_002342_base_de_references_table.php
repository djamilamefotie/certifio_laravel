<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// TABLE : base_references
// ------------------------------------------------------------
// Correspond à la classe "Base référence" du diagramme.
// C'est la base OFFICIELLE à laquelle on compare un diplôme
// soumis, gérée uniquement par l'Administrateur (relation
// "gère" du diagramme, 1..1).
// ============================================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('base_references', function (Blueprint $table) {
            // idReference -> id (clé primaire standard Laravel)
            $table->id();

            $table->string('numeroDiplome');
            $table->string('typeDiplome');
            $table->string('institution');
            // informationReference : champ texte libre pour toute
            // information complémentaire officielle (filière,
            // année, mention attendue, etc.)
            $table->text('informationReference')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('base_references');
    }
};
