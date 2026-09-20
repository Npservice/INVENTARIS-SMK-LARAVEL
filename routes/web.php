<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventarisLabelController;
use App\Http\Controllers\PeminjamanReceiptController;
use Illuminate\Support\Facades\Route;

// Halaman untuk pengunjung yang belum masuk.
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

// Semua halaman aplikasi memakai sesi login yang sama dengan route data di app.php.
Route::middleware('auth')->group(function (): void {
    Route::view('/', 'pages.dashboard.index')->middleware('permission:dashboard.index')->name('dashboard');

    Route::view('/inventaris', 'pages.inventaris.index')->middleware('permission:inventaris.index')->name('inventaris.index');
    Route::get('/inventaris/create', fn () => redirect()->route('inventaris.index', ['create' => 1]))->middleware('permission:inventaris.store')->name('inventaris.create');
    Route::get('/inventaris/pa', fn () => redirect()->route('inventaris.index'))->middleware('permission:inventaris.index')->name('inventaris.putra');
    Route::get('/inventaris/pi', fn () => redirect()->route('inventaris.index'))->middleware('permission:inventaris.index')->name('inventaris.putri');
    Route::view('/inventaris/{inventaris}', 'pages.inventaris.show')->middleware('permission:inventaris.show')->name('inventaris.show');
    Route::get('/inventaris/{inventaris}/label', [InventarisLabelController::class, 'show'])->middleware('permission:inventaris.show')->name('inventaris.label');

    Route::view('/gudang', 'pages.gudang.index')->middleware('permission:inventaris.index')->name('gudang');
    Route::view('/peminjaman', 'pages.peminjaman.index')->middleware('permission:peminjaman.index')->name('peminjaman');
    Route::view('/peminjaman/{peminjaman}', 'pages.peminjaman.show')->middleware('permission:peminjaman.show')->name('peminjaman.show');
    Route::get('/peminjaman/{peminjaman}/struk', [PeminjamanReceiptController::class, 'show'])->middleware('permission:peminjaman.show')->name('peminjaman.struk');
    Route::view('/laporan', 'pages.laporan.index')->middleware('permission:inventaris.index')->name('laporan');
    Route::view('/user', 'pages.user.index')->middleware('permission:user.index')->name('user');
    Route::view('/user/{user}', 'pages.user.show')->middleware('permission:user.show')->name('user.show');
    Route::view('/role-permission', 'pages.role.index')->middleware('permission:role.index')->name('role.index');
    Route::view('/role-permission/{role}', 'pages.role.show')->middleware('permission:role.show')->name('role.show');
    Route::view('/usulan-saran', 'pages.usulan.index')->middleware('permission:kritik-saran.index')->name('usulan');
    Route::view('/usulan-saran/{kritikSaran}', 'pages.usulan.show')->middleware('permission:kritik-saran.show')->name('usulan.show');

    Route::view('/master/instansi', 'pages.master.instansi.index')->middleware('permission:instansi.index')->name('master.instansi');
    Route::view('/master/lokasi', 'pages.master.lokasi.index')->middleware('permission:lokasi.index')->name('master.lokasi');
    Route::view('/master/jenis', 'pages.master.jenis.index')->middleware('permission:jenis.index')->name('master.jenis');
    Route::view('/master/pendanaan', 'pages.master.pendanaan.index')->middleware('permission:pendanaan.index')->name('master.pendanaan');
});
