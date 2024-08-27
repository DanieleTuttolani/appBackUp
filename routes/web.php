<?php

use App\Http\Controllers\DomainController;
use App\Models\Domain;
use Illuminate\Support\Facades\Route;

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

    $data = Domain::all();
    return view('welcome')->with('domains', $data);
});

Route::prefix('/domains')->group(function () {
    Route::post('/insert-domain', [DomainController::class, 'store'])->name('storeDomain');
    Route::put('/update-domain', [DomainController::class, 'update'])->name('updateDomain');
    Route::get('/mass-backup-creation', [DomainController::class, 'massCreation'])->name('massCreation');

});
Route::middleware('web')->group(function () {
    Route::get('scheduler-ui', function () {
        return view('scheduler-ui');
    });
});
