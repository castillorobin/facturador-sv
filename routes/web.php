<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DteController;
use App\Http\Controllers\DteExportController;
use App\Http\Controllers\NotaCreditoController;

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

    Route::post('/dtes/exportar-zip', [DteExportController::class, 'exportZip'])->name('dtes.exportZip');
    Route::get('/notas/create', [NotaCreditoController::class, 'create'])->name('notas.create');
    Route::post('/notas/store', [NotaCreditoController::class, 'store'])->name('notas.store');
    Route::delete('/dtes/{id}', [DteController::class, 'destroy'])->name('dtes.destroy');
    Route::post('/settings/toggle-contingencia', [DteController::class, 'toggleContingencia'])->name('settings.contingencia');

    // Ruta para MOSTRAR el formulario (la que cargaste ahorita)
Route::get('/contingencia/notificar', function () {
    return view('contingencia.evento');
})->name('contingencia.view_notificar');

// Ruta para PROCESAR el envío (la que el formulario busca en el botón Enviar)
Route::post('/contingencia/notificar', [App\Http\Controllers\DteController::class, 'notificarEvento'])
    ->name('contingencia.notificar');


    Route::post('/contingencia/transmitir', [App\Http\Controllers\DteController::class, 'transmitirPendientes'])
    ->name('contingencia.transmitir');






   
});

require __DIR__.'/auth.php';
