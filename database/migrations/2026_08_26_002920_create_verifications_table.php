
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// TABLE : verifications
// ------------------------------------------------------------
// Correspond à la classe "Vérification" du diagramme.
// C'est LE cœur du pipeline : chaque vérification lie UN
// diplôme soumis (relation "possède" : Diplomes 1..1 --- 0..*
// Vérification) à UNE entrée de la base de référence (relation
// "utilise" : Vérification 0..* --- 1..1 Base référence).
// C'est aussi ici qu'on stocke le résultat de l'OCR, de l'IA,
// et le score final calculé par la formule de Djamila.
// ============================================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verifications', function (Blueprint $table) {
            // idVérification -> id
            $table->id();

            $table->date('dateVérification');

            // statut : reprend les 3 états identifiés dans le
            // diagramme de cas d'utilisation ("Traiter vérification
            // ambigües" implique un état intermédiaire, en plus de
            // authentique/faux).
            $table->enum('statut', ['authentique', 'suspect', 'ambigu'])
                  ->default('ambigu');

            // resultat : texte libre pour le détail du résultat
            // (score final, explication, ou message renvoyé à
            // l'utilisateur).
            $table->text('resultat')->nullable();

            // --------------------------------------------------
            // Champs techniques du pipeline (pas dans le diagramme
            // UML tel quel, mais nécessaires pour stocker chaque
            // étape : OCR -> IA -> comparaison -> score).
            // Si tu préfères, on peut les enlever et ne garder que
            // "resultat" en JSON — dis-le-moi si tu veux ajuster.
            // --------------------------------------------------
            $table->text('texteOcrBrut')->nullable();       // texte extrait par Tesseract
            $table->json('donneesAnalyseIa')->nullable();    // réponse structurée de Gemini
            $table->decimal('scoreFinal', 5, 2)->nullable(); // score calculé par ta formule

            // --------------------------------------------------
            // Relations :
            //   Diplomes "1..1" --- possède ---> "0..*" Vérification
            //   Vérification "0..*" --- utilise ---> "1..1" Base référence
            // --------------------------------------------------
            $table->foreignId('diplome_id')
                  ->constrained('diplomes')
                  ->cascadeOnDelete();

            $table->foreignId('base_reference_id')
                  ->nullable() // peut être null si aucune correspondance trouvée
                  ->constrained('base_references')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifications');
    }
};