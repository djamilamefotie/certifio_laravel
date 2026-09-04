<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// TABLE paiements — CERTIFIO
// ------------------------------------------------------------
// Trace chaque tentative de paiement Monetbil : de l'initiation
// jusqu'à la confirmation (ou l'échec) via webhook.
// ============================================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Référence unique générée par Certifio, envoyée à Monetbil
            // en tant que "payment_ref". Sert à retrouver le paiement
            // quand le webhook nous notifie.
            $table->string('reference')->unique();

            // Référence de l'article vendu ("item_ref" côté Monetbil).
            // Ex: "abonnement_premium"
            $table->string('article_ref')->default('abonnement_premium');

            $table->unsignedInteger('montant');
            $table->string('devise', 10)->default('XAF');
            $table->string('telephone', 20)->nullable();
            $table->string('operateur', 50)->nullable();

            // en_attente -> reussi | echoue
            $table->string('statut')->default('en_attente');

            // Identifiant de transaction renvoyé par Monetbil, une fois connu.
            $table->string('transaction_id_monetbil')->nullable();

            // URL de paiement renvoyée par Monetbil au moment de l'initiation
            // (celle que l'app Flutter ouvre dans une WebView).
            $table->text('payment_url')->nullable();

            // Contenu brut de la notification webhook reçue, pour debug
            // et pour ajuster le parsing une fois le format exact connu.
            $table->json('donnees_webhook')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};