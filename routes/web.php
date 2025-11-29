<?php

use App\Enums\UserRole;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ManagerController;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    $user = auth()->user();

    if ($user->isManager()) {
        // Менеджер видит статистику только по своим клиентам
        $allClients = $user->clients()->count();
        $activeClients = $user->clients()->whereNotNull('manager_id')->count();

        return Inertia::render('Dashboard', [
            'statistics' => [
                'clients' => [
                    'active' => $activeClients,
                    'total' => $allClients,
                ],
            ],
        ]);
    }

    // Супер-менеджер и админ видят статистику в целом
    $allClients = Client::count();
    $activeClients = Client::whereNotNull('manager_id')->count();

    $allManagers = User::whereIn('role', [UserRole::MANAGER, UserRole::SUPER_MANAGER])->count();
    $activeManagers = User::whereIn('role', [UserRole::MANAGER, UserRole::SUPER_MANAGER])
        ->where('is_active', true)
        ->count();

    return Inertia::render('Dashboard', [
        'statistics' => [
            'clients' => [
                'active' => $activeClients,
                'total' => $allClients,
            ],
            'managers' => [
                'active' => $activeManagers,
                'total' => $allManagers,
            ],
        ],
    ]);
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
