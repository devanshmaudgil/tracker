<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TrackerController;
use App\Http\Controllers\MonthController;
use App\Http\Controllers\StaffUserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\CandidateController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('tracker.index');
    }
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/tracker/info', [TrackerController::class, 'index'])->name('tracker.index');
    Route::get('/create/demand', [TrackerController::class, 'create'])->name('tracker.create');
    Route::post('/tracker/info', [TrackerController::class, 'store'])->name('tracker.store');
    Route::get('/tracker/info/{id}/edit', [TrackerController::class, 'edit'])->name('tracker.edit');
    Route::get('/tracker/info/{id}/json', [TrackerController::class, 'show'])->name('tracker.show');
    Route::get('/tracker/info/{id}', [TrackerController::class, 'info'])->name('tracker.info');
    Route::put('/tracker/info/{id}', [TrackerController::class, 'update'])->name('tracker.update');
    Route::delete('/tracker/info/{id}', [TrackerController::class, 'destroy'])->name('tracker.destroy');
    
    // Candidate assignment and pipeline routes
    Route::post('/tracker/info/{id}/candidates', [TrackerController::class, 'assignCandidate'])->name('tracker.candidates.assign');
    Route::delete('/tracker/info/{tracker_id}/candidates/{tracker_candidate_id}', [TrackerController::class, 'unassignCandidate'])->name('tracker.candidates.unassign');
    Route::get('/tracker/info/{tracker_id}/candidates/{tracker_candidate_id}/pipeline', [TrackerController::class, 'getPipelineStatus'])->name('tracker.candidates.pipeline.get');
    Route::put('/tracker/info/{tracker_id}/candidates/{tracker_candidate_id}/pipeline', [TrackerController::class, 'updatePipelineStatus'])->name('tracker.candidates.pipeline.update');
    
    Route::get('/clients/info', [ClientController::class, 'info'])->name('clients.info');
    Route::post('/clients/info', [ClientController::class, 'store'])->name('clients.info.store');
    Route::get('/clients/info/{id}', [ClientController::class, 'show'])->name('clients.info.show');
    Route::put('/clients/info/{id}', [ClientController::class, 'update'])->name('clients.info.update');
    Route::delete('/clients/info/{id}', [ClientController::class, 'destroy'])->name('clients.info.destroy');
    
    Route::get('/region', [RegionController::class, 'index'])->name('regions.index');
    Route::post('/regions', [RegionController::class, 'store'])->name('regions.store');
    Route::get('/regions/{id}/edit', [RegionController::class, 'edit'])->name('regions.edit');
    Route::put('/regions/{id}', [RegionController::class, 'update'])->name('regions.update');
    Route::delete('/regions/{id}', [RegionController::class, 'destroy'])->name('regions.destroy');
    
    Route::get('/candidate/info', [CandidateController::class, 'index'])->name('candidates.index');
    Route::post('/candidates', [CandidateController::class, 'store'])->name('candidates.store');
    Route::get('/candidates/{id}/edit', [CandidateController::class, 'edit'])->name('candidates.edit');
    Route::put('/candidates/{id}', [CandidateController::class, 'update'])->name('candidates.update');
    Route::delete('/candidates/{id}', [CandidateController::class, 'destroy'])->name('candidates.destroy');
    
    Route::resource('months', MonthController::class);
    Route::resource('users', StaffUserController::class);
});
