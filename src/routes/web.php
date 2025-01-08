<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MailRegisterController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StatusController;

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
Route::middleware('auth')->group(function () {
    Route::get('/', [AuthController::class, 'index']);
    Route::get('/mail', [MailRegisterController::class, 'index']);
    Route::post('/update-email', [MailRegisterController::class, 'updateEmail']);
    Route::get('/schedule', [ScheduleController::class, 'index']);
    Route::get('/schedule/list', [ScheduleController::class, 'show'])->name('schedule.list');
    Route::get('/schedule/search', [ScheduleController::class, 'search'])->name('schedule.search');
    Route::get('/status', [StatusController::class, 'index']);
    Route::get('status/list', [StatusController::class, 'show'])->name('status.list');
    Route::post('/status/absence/confirm', [StatusController::class, 'confirmAbsence'])->name('status.absence.confirm');
    Route::post('/status/absence/store', [StatusController::class, 'storeAbsence'])->name('status.absence.store');

});
