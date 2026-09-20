<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InstansiController;
use App\Http\Controllers\Api\InventarisController;
use App\Http\Controllers\Api\JenisController;
use App\Http\Controllers\Api\KritikSaranController;
use App\Http\Controllers\Api\LokasiController;
use App\Http\Controllers\Api\PeminjamanController;
use App\Http\Controllers\Api\PendanaanController;
use App\Http\Controllers\Api\PerawatanController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| App API Routes
|--------------------------------------------------------------------------
|
| Full data API for the frontend (consumed via jQuery), running on the
| "web" guard/session — not a token-based API. Auth itself is handled
| separately outside this file. Every single endpoint carries its own
| Spatie permission (e.g. "inventaris.destroy"), enforced by the
| "permission" middleware alias.
|
*/

// Kotak usulan/saran publik — tidak butuh login, meniru perilaku app lama.
Route::post('kritik-saran', [KritikSaranController::class, 'store']);

Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard.index');

    Route::prefix('kritik-saran')->group(function () {
        Route::get('/', [KritikSaranController::class, 'index'])->middleware('permission:kritik-saran.index');
        Route::get('{kritikSaran}', [KritikSaranController::class, 'show'])->middleware('permission:kritik-saran.show');
        Route::delete('{kritikSaran}', [KritikSaranController::class, 'destroy'])->middleware('permission:kritik-saran.destroy');
    });

    Route::prefix('instansi')->group(function () {
        Route::get('select', [InstansiController::class, 'select'])->middleware('permission:instansi.select');
        Route::get('/', [InstansiController::class, 'index'])->middleware('permission:instansi.index');
        Route::post('/', [InstansiController::class, 'store'])->middleware('permission:instansi.store');
        Route::put('{instansi}', [InstansiController::class, 'update'])->middleware('permission:instansi.update');
        Route::delete('{instansi}', [InstansiController::class, 'destroy'])->middleware('permission:instansi.destroy');
    });

    Route::prefix('lokasi')->group(function () {
        Route::get('select', [LokasiController::class, 'select'])->middleware('permission:lokasi.select');
        Route::get('/', [LokasiController::class, 'index'])->middleware('permission:lokasi.index');
        Route::post('/', [LokasiController::class, 'store'])->middleware('permission:lokasi.store');
        Route::put('{lokasi}', [LokasiController::class, 'update'])->middleware('permission:lokasi.update');
        Route::delete('{lokasi}', [LokasiController::class, 'destroy'])->middleware('permission:lokasi.destroy');
    });

    Route::prefix('jenis')->group(function () {
        Route::get('select', [JenisController::class, 'select'])->middleware('permission:jenis.select');
        Route::get('/', [JenisController::class, 'index'])->middleware('permission:jenis.index');
        Route::post('/', [JenisController::class, 'store'])->middleware('permission:jenis.store');
        Route::put('{jenis}', [JenisController::class, 'update'])->middleware('permission:jenis.update');
        Route::delete('{jenis}', [JenisController::class, 'destroy'])->middleware('permission:jenis.destroy');
    });

    Route::prefix('pendanaan')->group(function () {
        Route::get('select', [PendanaanController::class, 'select'])->middleware('permission:pendanaan.select');
        Route::get('/', [PendanaanController::class, 'index'])->middleware('permission:pendanaan.index');
        Route::post('/', [PendanaanController::class, 'store'])->middleware('permission:pendanaan.store');
        Route::put('{pendanaan}', [PendanaanController::class, 'update'])->middleware('permission:pendanaan.update');
        Route::delete('{pendanaan}', [PendanaanController::class, 'destroy'])->middleware('permission:pendanaan.destroy');
    });

    Route::prefix('inventaris')->group(function () {
        Route::get('next-kode', [InventarisController::class, 'nextKode'])->middleware('permission:inventaris.next-kode');
        Route::get('export', [InventarisController::class, 'export'])->middleware('permission:inventaris.export');
        Route::get('import-template', [InventarisController::class, 'importTemplate'])->middleware('permission:inventaris.import-template');
        Route::post('import', [InventarisController::class, 'import'])->middleware('permission:inventaris.import');
        Route::get('select', [InventarisController::class, 'select'])->middleware('permission:inventaris.select');
        Route::get('/', [InventarisController::class, 'index'])->middleware('permission:inventaris.index');
        Route::get('{inventaris}', [InventarisController::class, 'show'])->middleware('permission:inventaris.show');
        Route::post('/', [InventarisController::class, 'store'])->middleware('permission:inventaris.store');
        Route::put('{inventaris}', [InventarisController::class, 'update'])->middleware('permission:inventaris.update');
        Route::delete('{inventaris}', [InventarisController::class, 'destroy'])->middleware('permission:inventaris.destroy');
    });

    Route::prefix('perawatan')->group(function () {
        Route::get('/', [PerawatanController::class, 'index'])->middleware('permission:perawatan.index');
        Route::get('{perawatan}', [PerawatanController::class, 'show'])->middleware('permission:perawatan.show');
        Route::post('/', [PerawatanController::class, 'store'])->middleware('permission:perawatan.store');
        Route::put('{perawatan}', [PerawatanController::class, 'update'])->middleware('permission:perawatan.update');
        Route::delete('{perawatan}', [PerawatanController::class, 'destroy'])->middleware('permission:perawatan.destroy');
    });

    Route::prefix('peminjaman')->group(function () {
        Route::get('find-by-kode', [PeminjamanController::class, 'findByKode'])->middleware('permission:peminjaman.find-by-kode');
        Route::get('/', [PeminjamanController::class, 'index'])->middleware('permission:peminjaman.index');
        Route::get('{peminjaman}', [PeminjamanController::class, 'show'])->middleware('permission:peminjaman.show');
        Route::post('/', [PeminjamanController::class, 'store'])->middleware('permission:peminjaman.store');
        Route::put('{peminjaman}', [PeminjamanController::class, 'update'])->middleware('permission:peminjaman.update');
        Route::post('{peminjaman}/kembali', [PeminjamanController::class, 'returnItem'])->middleware('permission:peminjaman.kembali');
        Route::delete('{peminjaman}', [PeminjamanController::class, 'destroy'])->middleware('permission:peminjaman.destroy');
    });

    Route::prefix('user')->group(function () {
        Route::get('select', [UserController::class, 'select'])->middleware('permission:user.select');
        Route::get('/', [UserController::class, 'index'])->middleware('permission:user.index');
        Route::get('{user}', [UserController::class, 'show'])->middleware('permission:user.show');
        Route::post('/', [UserController::class, 'store'])->middleware('permission:user.store');
        Route::put('{user}', [UserController::class, 'update'])->middleware('permission:user.update');
        Route::delete('{user}', [UserController::class, 'destroy'])->middleware('permission:user.destroy');
    });

    Route::get('permission', [RoleController::class, 'permissions'])->middleware('permission:permission.index');

    Route::prefix('role')->group(function () {
        Route::get('select', [RoleController::class, 'select'])->middleware('permission:role.select');
        Route::get('/', [RoleController::class, 'index'])->middleware('permission:role.index');
        Route::get('{role}', [RoleController::class, 'show'])->middleware('permission:role.show');
        Route::post('/', [RoleController::class, 'store'])->middleware('permission:role.store');
        Route::put('{role}', [RoleController::class, 'update'])->middleware('permission:role.update');
        Route::delete('{role}', [RoleController::class, 'destroy'])->middleware('permission:role.destroy');
    });
});
