<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PlantillaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Middleware\DepartmentAccess;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Playground (for testing)
Route::get('/playground', fn() => Inertia::render('Playground'))->name('playground');

// Default home → redirect to login
Route::get('/', fn() => redirect()->route('login'))->name('home');

// Laravel/Inertia login + logout
Route::get('/login', fn() => Inertia::render('Auth/Login'))->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/change-password', [LoginController::class, 'changePassword'])
    ->middleware('auth');

Route::middleware(['auth'])->group(function () {

    Route::resource('employees', EmployeeController::class);

});


// HR dashboard → still an Inertia page (not Sneat)
Route::middleware(['auth', DepartmentAccess::class])->group(function () {
    Route::get('/hr-dashboard', fn() => Inertia::render('Departments/HR/Dashboard'))
        ->name('hr.dashboard');
});

// Sneat dashboard entry point
Route::middleware(['auth'])->group(function () {
    // 👇 Any path starting with /build loads Sneat
    Route::get('/build/{any?}', function () {
        return view('application'); // Sneat’s SPA entrypoint (Blade view)
    })->where('any', '.*')->name('sneat.dashboard');
});

// 👇 Catch-all (optional, keep last to avoid conflicts)
Route::get('{any}', function () {
    return Inertia::render('Errors/NotFound');
})->where('any', '.*');
