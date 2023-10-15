<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\InvitadoController;
use App\Http\Controllers\ArtefactoController;
use App\Http\Controllers\DiagramadorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('diagramador', DiagramadorController::class);
    Route::get('invitar/{diagramador}', [DiagramadorController::class, 'invitar'])->name('invitar');
    Route::Post('registrarInvitado', [DiagramadorController::class, 'registrarInvitado'])->name('registrarInvitado');


    // Route::get('exportarCodigoZip', [DiagramadorController::class, 'exportarCodigoZip'])->name('exportarCodigoZip');
    Route::get('codeJava/{diagramador}', [DiagramadorController::class, 'codeJava'])->name('codeJava');
    Route::get('codePy/{diagramador}', [DiagramadorController::class, 'codePy'])->name('codePy');
    Route::get('codePhp/{diagramador}', [DiagramadorController::class, 'codePhp'])->name('codePhp');
    Route::post('exportarCase/{diagramador}', [DiagramadorController::class, 'exportarCase'])->name('exportarCase');

    Route::Post('artefactoStore', [ArtefactoController::class, 'store'])->name('artefactoStore');
    Route::Post('linkStore', [LinkController::class, 'store'])->name('linkStore');

    Route::Post('invitadoDelete', [InvitadoController::class,'invitadoDelete'])->name('invitadoDelete');
});
