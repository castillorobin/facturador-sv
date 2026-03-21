<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Rutas para configuración de empresa
    Route::get('/company/setup', [CompanyController::class, 'edit'])->name('company.edit');
    Route::patch('/company/setup', [CompanyController::class, 'update'])->name('company.update');
    // Rutas para gestión de clientes
    Route::resource('customers', CustomerController::class);
});

require __DIR__.'/auth.php';
