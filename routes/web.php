<?php

use App\Http\Controllers\Cheques\EntregaController;
use App\Http\Controllers\Rubricas\RubricaController;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Administracion\Roles;
use App\Http\Controllers\Roles\RolController;
use App\Http\Controllers\Cheques\ChequesController;
use App\Http\Controllers\Periodos\PeriodoController;
use App\Http\Controllers\Tribunales\TribunalesController;
use App\Http\Controllers\Users\UserController;
use App\Http\Livewire\Periodos\Profile as ProfilePeriodos;
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


    Route::prefix('roles')->namespace('Roles')->name('roles.')->group(function () {
        // Route::get('/roles', [RolController::class, 'index'])->name('index');
        Route::view('/', 'livewire.roles.index');
        Route::put('/updatePermisos/{id}', [RolController::class, 'updatePermisos'])->name('updatePermisos');
    });

    // Route::middleware(['can:Permisos - Seccion'])->prefix('permissions')->namespace('Permissions')->name('permissions.')->group(function () {
    //     Route::view('/', 'livewire.permissions.index');
    // });
    Route::prefix('permissions')->namespace('Permissions')->name('permissions.')->group(function () {
        Route::view('/', 'livewire.permissions.index');
    });

    Route::prefix('users')->namespace('Users')->name('users.')->group(function () {
        Route::view('/', 'livewire.users.index');
        Route::get('/profile/{id}', [UserController::class, 'profile'])->name('profile');
        Route::put('/updateRoles/{id}', [UserController::class, 'updateRoles'])->name('updateRoles');
        Route::get('/exitImpersonate/', [UserController::class, 'exitImpersonate'])->name('exitImpersonate');
    });

    Route::prefix('periodos')->namespace('Periodos')->name('periodos.')->group(function () {
        Route::view('/', 'livewire.periodos.index');
        Route::get('/{id}', [PeriodoController::class, 'show'])->name('profile');
        Route::get('/tribunales/{carreraPeriodoId}', [TribunalesController::class, 'index'])->name('tribunales.index');
    });

    Route::prefix('carreras')->namespace('Carreras')->name('carreras.')->group(function () {
        Route::view('/', 'livewire.carreras.index');
    });

    Route::prefix('estudiantes')->namespace('Estudiantes')->name('estudiantes.')->group(function () {
        Route::view('/', 'livewire.estudiantes.index');
    });

    Route::prefix('tribunales')->namespace('Tribunales')->name('tribunales.')->group(function () {
        Route::get('/componentes/{componenteId}', [TribunalesController::class, 'componenteShow'])->name('componente.show');
    });

    Route::prefix('rubricas')->namespace('Rubricas')->name('rubricas.')->group(function () {
        Route::view('/', 'livewire.rubricas.index');
        Route::get('/create', [RubricaController::class, 'create'])->name('create');
        Route::get('/edit/{id}', [RubricaController::class, 'edit'])->name('edit');
    });
});
