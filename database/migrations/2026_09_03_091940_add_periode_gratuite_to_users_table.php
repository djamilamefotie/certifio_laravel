<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'abonnement_expire_le')) {
                $table->timestamp('abonnement_expire_le')->nullable()->after('abonnement');
            }
            if (!Schema::hasColumn('users', 'periode_gratuite_debut')) {
                $table->timestamp('periode_gratuite_debut')->nullable()->after('abonnement_expire_le');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['abonnement_expire_le', 'periode_gratuite_debut']);
        });
    }
};
