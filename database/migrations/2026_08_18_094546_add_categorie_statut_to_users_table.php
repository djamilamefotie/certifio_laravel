<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('categorie', ['administrateur', 'utilisateur', 'institut'])
                  ->default('utilisateur')
                  ->after('email');

            $table->enum('statut', ['actif', 'bloque'])
                  ->default('actif')
                  ->after('categorie');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['categorie', 'statut']);
        });
    }
};
