<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('base_references', function (Blueprint $table) {
            $table->string('nomTitulaire')->nullable()->after('institution');
            $table->date('dateNaissance')->nullable()->after('nomTitulaire');
            $table->string('lieuNaissance')->nullable()->after('dateNaissance');
            $table->string('etablissement')->nullable()->after('lieuNaissance');
            $table->string('centreExamen')->nullable()->after('etablissement');
            $table->date('dateObtention')->nullable()->after('centreExamen');
            $table->string('session')->nullable()->after('dateObtention');
            $table->date('dateDelivrance')->nullable()->after('session');
            $table->string('lieuDelivrance')->nullable()->after('dateDelivrance');
            $table->string('matricule')->nullable()->after('lieuDelivrance');
            $table->string('mention')->nullable()->after('matricule');
            $table->string('serieOuFiliere')->nullable()->after('mention');
            $table->json('informationsComplementaires')->nullable()->after('serieOuFiliere');
        });
    }

    public function down(): void
    {
        Schema::table('base_references', function (Blueprint $table) {
            $table->dropColumn([
                'nomTitulaire', 'dateNaissance', 'lieuNaissance', 'etablissement',
                'centreExamen', 'dateObtention', 'session', 'dateDelivrance',
                'lieuDelivrance', 'matricule', 'mention', 'serieOuFiliere',
                'informationsComplementaires',
            ]);
        });
    }
};