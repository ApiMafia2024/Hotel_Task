<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HotelController;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/hotels', [HotelController::class, 'index'])->name('hotels.index');
Route::get('/hotels/{hotel}', [HotelController::class, 'show']);
Route::post('/hotels', [HotelController::class, 'store']);
Route::put('/hotels/{hotel}', [HotelController::class, 'update'])->name('hotels.update');
Route::delete('/hotels/{hotel}', [HotelController::class, 'destroy']);
Route::post('hotels/bulk-update', [HotelController::class, 'bulkUpdate']);
Route::post('/hotels/bulk-delete', [HotelController::class, 'bulkDestroy']);
