<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\RendezVousController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);

    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    
    // Dashboard
    Route::get('dashboard', function() {
        $demandes = auth()->user()->demandes()->with('citoyen')->orderBy('created_at', 'desc')->get();
        return view('dashboard', compact('demandes'));
    })->name('dashboard');

    // Demandes
    Route::get('demandes', [DemandeController::class, 'index'])->name('demandes.index');
    Route::get('demandes/nouvelle', [DemandeController::class, 'create'])->name('demandes.create');
    Route::post('demandes', [DemandeController::class, 'store'])->name('demandes.store');
    Route::get('demandes/{demande}', [DemandeController::class, 'show'])->name('demandes.show');

    // Documents
    Route::get('documents/{document}/download', [\App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');

    // Rendez-vous
    Route::get('rendez-vous', [RendezVousController::class, 'index'])->name('rendezvous.index');
    Route::get('demandes/{demande}/rendez-vous/nouveau', [RendezVousController::class, 'create'])->name('rendezvous.create');
    Route::post('demandes/{demande}/rendez-vous', [RendezVousController::class, 'store'])->name('rendezvous.store');
    Route::delete('rendez-vous/{rendezVous}', [RendezVousController::class, 'destroy'])->name('rendezvous.destroy');

    // API : créneaux occupés (AJAX)
    Route::get('api/rendezvous/creneaux-occupes', [RendezVousController::class, 'getOccupiedSlots'])->name('rendezvous.occupied-slots');

    // Espace Agent (Back-office)
    Route::middleware('role:AGENT')->prefix('agent')->name('agent.')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Agent\AgentController::class, 'dashboard'])->name('dashboard');
        Route::get('demandes', [\App\Http\Controllers\Agent\AgentController::class, 'index'])->name('demandes.index');
        Route::get('demandes/{demande}', [\App\Http\Controllers\Agent\AgentController::class, 'show'])->name('demandes.show');
        Route::post('demandes/{demande}/instruire', [\App\Http\Controllers\Agent\AgentController::class, 'instruire'])->name('demandes.instruire');
        Route::post('demandes/{demande}/valider', [\App\Http\Controllers\Agent\AgentController::class, 'valider'])->name('demandes.valider');
        Route::post('demandes/{demande}/rejeter', [\App\Http\Controllers\Agent\AgentController::class, 'rejeter'])->name('demandes.rejeter');
    });
});
