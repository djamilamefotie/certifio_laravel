<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

// Connexion automatique de l'admin mobile à Filament :
// l'app envoie son token Sanctum (en-tête Authorization), Laravel ouvre la
// session web puis redirige vers le panel Filament.
Route::get('/mobile-admin-login', function (Request $request) {
    $user = $request->user(); // identifié par le token Sanctum

    abort_unless($user && $user->categorie === 'administrateur', 403);

    Auth::guard('web')->login($user);
    $request->session()->regenerate();

    return redirect('/admin');
})->middleware('auth:sanctum')->name('mobile-admin-login');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
    });

Route::middleware(['auth'])->group(function () {
    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

require __DIR__.'/settings.php';