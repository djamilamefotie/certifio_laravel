<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offre_abonnements', function (Blueprint $table) {
            $table->enum('type', ['gratuit', 'premium'])->default('premium')->after('nom');
            $table->unsignedInteger('limite_verifications')->nullable()->after('duree_jours');
        });
    }

    public function down(): void
    {
        Schema::table('offre_abonnements', function (Blueprint $table) {
            $table->dropColumn(['type', 'limite_verifications']);
        });
    }
};