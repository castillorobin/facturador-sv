<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DteController;
use App\Http\Controllers\DteExportController;
use App\Http\Controllers\NotaCreditoController;
use App\Http\Controllers\ContingenciaController;

use App\Models\Dte;


Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $company_id = auth()->user()->company_id;
    $mesActual = now()->month;
    $anioActual = now()->year;

    // 1. Total de Ventas del mes actual (Solo facturas procesadas con éxito)
    $totalVentasMes = Dte::where('company_id', $company_id)
        ->whereMonth('fecha_emision', $mesActual)
        ->whereYear('fecha_emision', $anioActual)
        ->where('estado', 'PROCESADO')
        ->sum('total_pagar');

    // 2. Conteo de documentos emitidos en el mes
    $conteoDtesMes = Dte::where('company_id', $company_id)
        ->whereMonth('fecha_emision', $mesActual)
        ->whereYear('fecha_emision', $anioActual)
        ->count();

    // 3. Alertas de Contingencia (DTEs que necesitan atención)
    $pendientesContingencia = Dte::where('company_id', $company_id)
        ->whereIn('estado', ['CONTINGENCIA', 'REPORTADO'])
        ->count();

    // 4. Los últimos 5 DTEs para la tabla de actividad
    $ultimosDtes = Dte::where('company_id', $company_id)
        ->with('customer') // Eager loading para evitar el problema N+1
        ->latest()
        ->take(5)
        ->get();

    return view('dashboard', compact(
        'totalVentasMes', 
        'conteoDtesMes', 
        'pendientesContingencia', 
        'ultimosDtes'
    ));
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
    

    // Ruta para MOSTRAR el formulario (la que cargaste ahorita)
Route::get('/contingencia/notificar', function () {
    return view('contingencia.evento');
})->name('contingencia.view_notificar');

// Ruta para PROCESAR el envío (la que el formulario busca en el botón Enviar)
Route::post('/contingencia/notificar', [App\Http\Controllers\DteController::class, 'notificarEvento'])
    ->name('contingencia.notificar');

    
    // Rutas para las acciones individuales
    Route::post('/dtes/{id}/reportar', [DteController::class, 'reportarIndividual'])->name('dtes.reportar');
    Route::post('/dtes/{id}/enviar-contingencia', [DteController::class, 'enviarIndividual'])->name('dtes.enviar');
    Route::post('/settings/contingencia', [ContingenciaController::class, 'toggleModo'])->name('settings.contingencia');




   
});

require __DIR__.'/auth.php';
