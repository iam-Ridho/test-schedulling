<?php

use App\Http\Controllers\DosenController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\KeahlianController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\SesiController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/mahasiswa');

Route::resource('mahasiswa', MahasiswaController::class);
Route::get('dosen/keahlian/{keahlianId}', [MahasiswaController::class, 'getDosenByKeahlian'])->name('dosen.byKeahlian');

Route::get('jadwal-ujian', [MahasiswaController::class, 'jadwalIndex'])->name('mahasiswa.jadwal');
Route::get('jadwal-ujian/export', [MahasiswaController::class, 'exportJadwal'])->name('mahasiswa.jadwal.export');
Route::post('jadwal-ujian/generate', [MahasiswaController::class, 'generateJadwal'])->name('mahasiswa.jadwal.generate');
Route::delete('jadwal-ujian/clear', [MahasiswaController::class, 'clearJadwal'])->name('mahasiswa.jadwal.clear');
Route::get('api/ortools/health', [MahasiswaController::class, 'apiHealthStatus'])->name('api.ortools.health');

Route::resource('keahlian', KeahlianController::class);

Route::resource('dosen', DosenController::class);

Route::resource('sesi', SesiController::class);

Route::resource('ruangan', RuanganController::class);