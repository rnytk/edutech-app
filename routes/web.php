<?php

use App\Http\Controllers\Autenticacion\SesionEstudianteController;
use App\Livewire\Autenticacion\IniciarSesion;
use App\Livewire\Portal\InicioTemporal;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', IniciarSesion::class)->name('estudiante.login');
Route::post('/login', [SesionEstudianteController::class, 'autenticar'])->name('estudiante.login.autenticar');

Route::middleware(['auth', 'estudiante.activo'])->group(function (): void {
    Route::get('/dashboard', InicioTemporal::class)->name('portal.inicio');
    Route::post('/logout', [SesionEstudianteController::class, 'cerrar'])->name('estudiante.logout');
});
