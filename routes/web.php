<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\EvacuationCenterController;

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

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])
    ->name('home');

Auth::routes();
Auth::routes(['register' => false]);
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
    ->name('home');

Route::get('/gsis', [App\Http\Controllers\GsisController::class, 'index'])
    ->name('gsis.index');
Route::get('/barangays', [App\Http\Controllers\BarangayController::class, 'index'])
    ->name('barangays.index');
Route::get('/barangays/all/locations', [App\Http\Controllers\BarangayController::class, 'fetchLocations'])
    ->name('barangays.fetch-locations');
Route::get('/barangays/{barangay}/open', [App\Http\Controllers\BarangayController::class, 'getBarangay'])
    ->name('barangays.open');
Route::get('/calamity', [App\Http\Controllers\CalamityController::class, 'index'])
    ->name('calamity.index');

Route::middleware('auth')->group(function () {
    Route::get('mdrrmo', [\App\Http\Controllers\MdrrmoController::class, 'index'])
        ->name('mdrrmo.centers');
    Route::get('mdrrmo/generate-report', [\App\Http\Controllers\CsvController::class, 'generateReport'])
        ->name('mdrrmo.generate-report');
    Route::get('bdrrmo/generate-pdf', [\App\Http\Controllers\PDFController::class, 'generatePDF'])
        ->name('bdrrmo.generate-pdf');
    Route::get('bdrrmo/{bdrrmo}/center', [EvacuationCenterController::class, 'getCenter'])
        ->name('bdrrmo.center');

    Route::post('/calamity', [App\Http\Controllers\CalamityController::class, 'store'])
        ->name('calamity.store');

    Route::middleware('super_admin')->group(function () {
        Route::resource('bdrrmo', EvacuationCenterController::class);
    });
});
