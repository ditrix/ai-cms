<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ManagerController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('dashboard')->group(function () {
    // Маршруты для клиентов
    Route::post('clients/{client}/change-manager', [ClientController::class, 'changeManager'])
        ->name('clients.change-manager');
    Route::resource('clients', ClientController::class);

    // Маршруты для менеджеров
    Route::post('managers/{manager}/change-password', [ManagerController::class, 'changePassword'])
        ->name('managers.change-password');
    Route::post('managers/{manager}/toggle-active', [ManagerController::class, 'toggleActive'])
        ->name('managers.toggle-active');
    Route::resource('managers', ManagerController::class);
});

require __DIR__.'/settings.php';
