<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// AJOUTE LES COLONNES D'ABONNEMENT SUR LA TABLE users
// ------------------------------------------------------------
// On utilise Schema::hasColumn() avant chaque ajout pour éviter
// une erreur si une colonne existe déjà (ex: 'abonnement' avait
// peut-être déjà été ajoutée manuellement).
// ============================================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'abonnement')) {
                // 'gratuit' ou 'premium'
                $table->string('abonnement')->default('gratuit')->after('email');
            }

            if (!Schema::hasColumn('users', 'abonnement_expire_le')) {
                // Date à laquelle l'abonnement premium expire.
                // null si l'utilisateur est en gratuit ou n'a jamais payé.
                $table->timestamp('abonnement_expire_le')->nullable()->after('abonnement');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'abonnement_expire_le')) {
                $table->dropColumn('abonnement_expire_le');
            }
            if (Schema::hasColumn('users', 'abonnement')) {
                $table->dropColumn('abonnement');
            }
        });
    }
};