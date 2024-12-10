<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangKategoriController;
use App\Http\Controllers\BarangTransaksiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * Manage Barang
 * =============================================================================
 */

Route::post('/login', [AuthController::class, 'login']);
Route::middleware(['auth:sanctum'])->get('/profile', function (Request $request) {
    return response()->json($request->user());
});

Route::middleware(['auth:sanctum'])->put('/profile', [AuthController::class, 'updateProfile']);

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::post('/register', [AuthController::class, 'register']);

Route::prefix('barang')->name('barang.')->group(function () {
    // kategori
    Route::prefix('kategori')->name('kategori.')->group(function () {
        Route::get('/', [BarangKategoriController::class, 'list'])
            ->name('list');
        Route::post('/', [BarangKategoriController::class, 'store'])
            ->name('store');
        Route::get('{id}', [BarangKategoriController::class, 'show'])
            ->name('show');
        Route::put('{id}', [BarangKategoriController::class, 'update'])
            ->name('update');
        Route::delete('{id}', [BarangKategoriController::class, 'delete'])
            ->name('delete');
    });

    // barang
    Route::get('/', [BarangController::class, 'list'])
        ->name('list');
    Route::post('/', [BarangController::class, 'store'])
        ->name('store');
    Route::get('{id}', [BarangController::class, 'show'])
        ->name('show');
    Route::put('{id}', [BarangController::class, 'update'])
        ->name('update');
    Route::delete('{id}', [BarangController::class, 'delete'])
        ->name('delete');
});

/**
 * Transaksi
 * =============================================================================
 */

Route::prefix('transaksi')->name('transaksi.')->group(function () {
    Route::get('/', [BarangTransaksiController::class, 'list'])
        ->name('list');
    Route::post('/', [BarangTransaksiController::class, 'store'])
        ->name('store');
    Route::get('{id}', [BarangTransaksiController::class, 'show'])
        ->name('show');
});
