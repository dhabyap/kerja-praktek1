<?php

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionExportController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingExportController;


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
    return redirect('/admin/login');
});

Route::get('/admin/bookings/{booking}/download-invoice', [InvoiceController::class, 'download'])
    ->name('booking.download.invoice');

Route::get('/booking/export', [BookingExportController::class, 'export'])->name('booking.export');
Route::get('/transaksi/export', [TransactionExportController::class, 'export'])->name('transaksi.export');

Route::get('/units/{unit}', [UnitController::class, 'show'])->name('unit.detail');


// Route::get('admin', [DashboardController::class, 'index'])->name('filament.admin.pages.dashboard');


// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

require __DIR__ . '/auth.php';
