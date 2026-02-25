<?php

use App\Http\Controllers\Admin\EmpleadoTemporalController;
use App\Http\Controllers\Public\PortalPublicoController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ---------------------------------------------------------------------------
// Ruta raíz → redirige al login
// ---------------------------------------------------------------------------
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// ---------------------------------------------------------------------------
// Dashboard (requiere autenticación y verificación de email)
// ---------------------------------------------------------------------------
Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ---------------------------------------------------------------------------
// Fase 3: Panel de Administración — requiere autenticación
// ---------------------------------------------------------------------------
Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Empleados Temporales
        Route::get('/empleados-temporales', [EmpleadoTemporalController::class, 'index'])
            ->name('empleados-temporales.index');
        Route::post('/empleados-temporales', [EmpleadoTemporalController::class, 'store'])
            ->name('empleados-temporales.store');
        Route::delete('/empleados-temporales/{empleadoTemporal}', [EmpleadoTemporalController::class, 'destroy'])
            ->name('empleados-temporales.destroy');
    });

// ---------------------------------------------------------------------------
// Fase 4: Portal Público — sin autenticación
// ---------------------------------------------------------------------------
Route::prefix('publico')
    ->name('publico.')
    ->group(function () {

        // Vistas Inertia
        Route::get('/', [PortalPublicoController::class, 'menu'])->name('menu');
        Route::get('/asistencia', [PortalPublicoController::class, 'registroAsistencia'])->name('asistencia');
        Route::get('/retardo', [PortalPublicoController::class, 'registroRetardo'])->name('retardo');
        Route::get('/visitas', [PortalPublicoController::class, 'registroVisitas'])->name('visitas');

        // Guardar registro (POST — sin CSRF issues porque viene del mismo dominio)
        Route::post('/guardar-registro', [PortalPublicoController::class, 'guardarRegistro'])
            ->name('guardar-registro');

        // Endpoints JSON para búsqueda AJAX
        Route::get('/api/buscar-por-tag', [PortalPublicoController::class, 'buscarPorTag'])
            ->name('api.buscar-por-tag');
        Route::get('/api/buscar-por-nombre', [PortalPublicoController::class, 'buscarPorNombre'])
            ->name('api.buscar-por-nombre');
        Route::get('/api/registros-hoy', [PortalPublicoController::class, 'registrosHoy'])
            ->name('api.registros-hoy');
    });

require __DIR__ . '/settings.php';
