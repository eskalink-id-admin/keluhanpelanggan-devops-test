<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeploymentController;
use App\Http\Controllers\DeploymentNoteController;
use App\Models\DeploymentNote;
use App\Support\ReleaseInfo;
use Illuminate\Http\Request;

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
    return view('welcome');
});

Auth::routes();

Route::withoutMiddleware('web')->group(function () {
    Route::get('/health', [DeploymentController::class, 'health']);
    Route::get('/ready', [DeploymentController::class, 'ready']);
    Route::get('/version', [DeploymentController::class, 'version']);
});

Route::middleware('auth')->group(function () {
    Route::get('/deployment', function (Request $request) {
        return view('deployment', [
            'release' => ReleaseInfo::current(),
            'notes' => DeploymentNote::where('user_id', $request->user()->id)->latest('id')->paginate(10),
        ]);
    })->name('deployment');
    Route::post('/deployment/notes', [DeploymentNoteController::class, 'store'])->name('deployment.notes.store');
    Route::delete('/deployment/notes/{note}', [DeploymentNoteController::class, 'destroy'])->name('deployment.notes.destroy');
});

Route::get('/{any}', [App\Http\Controllers\HomeController::class, 'index'])
    ->where('any', '.*')
    ->name('home');
