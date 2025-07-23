<?php

use App\Http\Controllers\Cheques\EntregaController;
use App\Http\Controllers\Rubricas\RubricaController;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Administracion\Roles;
use App\Http\Controllers\Roles\RolController;
use App\Http\Controllers\Cheques\ChequesController;
use App\Http\Controllers\Periodos\PeriodoController;
use App\Http\Controllers\PlanEvaluacionController;
use App\Http\Controllers\Tribunales\TribunalesController;
use App\Http\Controllers\Users\UserController;
use App\Http\Livewire\Periodos\Profile as ProfilePeriodos;
use App\Http\Livewire\PlanEvaluacionManager;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    // return view('welcome');
    return view('home');
})->middleware('auth');


Auth::routes();

Route::impersonate();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->middleware('auth')->name('home');

Route::middleware('auth')->group(function () {

    // === ROLES & PERMISOS (Solo Super Admin) ===
    Route::middleware(['permission:gestionar roles y permisos'])->prefix('roles')->namespace('Roles')->name('roles.')->group(function () {
        Route::view('/', 'livewire.roles.index');
        Route::put('/updatePermisos/{id}', [RolController::class, 'updatePermisos'])->name('updatePermisos');
    });

    Route::middleware(['permission:gestionar roles y permisos'])->prefix('permissions')->namespace('Permissions')->name('permissions.')->group(function () {
        Route::view('/', 'livewire.permissions.index');
    });

    // === USUARIOS/PROFESORES (Super Admin y Administrador) ===
    Route::middleware(['permission:gestionar usuarios'])->prefix('users')->namespace('Users')->name('users.')->group(function () {
        Route::view('/', 'livewire.users.index');
        Route::get('/profile/{id}', [UserController::class, 'profile'])->name('profile');
        Route::put('/updateRoles/{id}', [UserController::class, 'updateRoles'])->name('updateRoles');
        Route::get('/exitImpersonate/', [UserController::class, 'exitImpersonate'])->name('exitImpersonate');
    });

    // === CARRERAS (Super Admin y Administrador) ===
    Route::middleware(['permission:gestionar carreras'])->prefix('carreras')->namespace('Carreras')->name('carreras.')->group(function () {
        Route::view('/', 'livewire.carreras.index');
    });

    // === ESTUDIANTES (Contextual según carrera-período) ===
    Route::prefix('estudiantes')->namespace('Estudiantes')->name('estudiantes.')->group(function () {
        // Verificación de permisos básicos - la verificación contextual se hará en los componentes Livewire
        Route::view('/', 'livewire.estudiantes.index');
    });

    // === RÚBRICAS (Contextual según carrera-período) ===
    Route::prefix('rubricas')->namespace('Rubricas')->name('rubricas.')->group(function () {
        // Listado - verificación contextual en el componente
        Route::view('/', 'livewire.rubricas.index');
        Route::get('/create', [RubricaController::class, 'create'])->name('create');
        Route::get('/edit/{id}', [RubricaController::class, 'edit'])->name('edit');
    });

    // === PERÍODOS (Verificación contextual en componentes) ===
    Route::prefix('periodos')->namespace('Periodos')->name('periodos.')->group(function () {
        // Lista de períodos - verificación contextual en el componente (Super Admin, Administrador, Director, Apoyo)
        Route::view('/', 'livewire.periodos.index');
        // Profile de período - verificación contextual en el controlador
        Route::get('/{id}', [PeriodoController::class, 'show'])->name('profile');
        // Tribunales de carrera-período - verificación contextual en controlador
        Route::get('/tribunales/{carreraPeriodoId}', [TribunalesController::class, 'index'])->name('tribunales.index');
        Route::get('/tribunales/profile/{tribunalId}', [TribunalesController::class, 'profile'])->name('tribunales.profile');
    });

    // === TRIBUNALES (Verificación contextual en controladores) ===
    Route::prefix('tribunales')->namespace('Tribunales')->name('tribunales.')->group(function () {
        // Dashboard de tribunales - todos los roles pueden acceder para ver SUS tribunales
        Route::get('/', [TribunalesController::class,'principal'])->name('principal');
        // Calificación - verificación contextual en el controlador
        Route::get('/calificar/{tribunalId}', [TribunalesController::class,'calificar'])->name('calificar');
    });

    // === PLANES DE EVALUACIÓN (Contextual según carrera-período) ===
    Route::prefix('planes-evaluacion')->name('planes_evaluacion.')->group(function () {
        // Verificación contextual en el controlador usando Gates
        Route::get('/manage/{carreraPeriodoId}', [PlanEvaluacionController::class, 'manage'])->name('manage');
    });

    // Ruta para descargar archivos PDF temporales
    Route::get('/download-temp-pdf/{filename}', function ($filename) {
        $path = storage_path('app/temp/' . $filename);

        if (!file_exists($path)) {
            abort(404, 'Archivo no encontrado');
        }

        return response()->download($path)->deleteFileAfterSend(true);
    })->name('download.temp.pdf');
});
