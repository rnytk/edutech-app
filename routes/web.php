<?php

use App\Http\Controllers\Autenticacion\SesionEstudianteController;
use App\Livewire\Autenticacion\IniciarSesion;
use App\Livewire\Portal\BienvenidaCurso;
use App\Livewire\Portal\Dashboard;
use App\Livewire\Portal\NivelesCurso;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', IniciarSesion::class)->name('estudiante.login');
Route::post('/login', [SesionEstudianteController::class, 'autenticar'])->name('estudiante.login.autenticar');

Route::middleware(['auth', 'estudiante.activo'])->group(function (): void {
    Route::get('/dashboard', Dashboard::class)->name('portal.inicio');
    Route::get('/cursos/{curso}', BienvenidaCurso::class)->name('cursos.bienvenida');
    Route::get('/cursos/{curso}/niveles', NivelesCurso::class)->name('cursos.niveles');
    Route::post('/logout', [SesionEstudianteController::class, 'cerrar'])->name('estudiante.logout');
});
