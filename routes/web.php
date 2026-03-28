<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DteController;

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
    Route::resource('products', ProductController::class);

    Route::post('dtes/{id}/enviar', [DteController::class, 'enviarAHacienda'])->name('dtes.enviar');
    Route::resource('dtes', DteController::class);

    Route::get('dtes/{id}/download-json', [DteController::class, 'downloadJson'])->name('dtes.downloadJson');

    Route::get('dtes/{id}/ver-pdf', [DteController::class, 'verPdf'])->name('dtes.verPdf');
    Route::post('dtes/{id}/reenviar-correo', [DteController::class, 'reenviarCorreo'])->name('dtes.reenviarCorreo');
    Route::post('dtes/{id}/anular', [DteController::class, 'anular'])->name('dtes.anular');
});

require __DIR__.'/auth.php';
