<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MailRegisterController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TeacherAuthController;
use App\Http\Controllers\TeacherScheduleController;
use App\Http\Controllers\TeacherClassController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\NoticeController;


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

Route::prefix('teacher')->group(function () {
    Route::get('/login', [TeacherAuthController::class, 'showLoginForm'])->name('teacher.login');
    Route::post('/login', [TeacherAuthController::class, 'login'])->name('teacher.login.submit');
});

Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
});

Route::middleware(['auth:teacher'])->group(function () {
    Route::get('/teacher', [TeacherAuthController::class, 'index'])->name('teacher.teacher');
    Route::get('/search',[TeacherScheduleController::class, 'showForm'])->name('teacher.search');
    Route::post('/search/result', [TeacherScheduleController::class, 'result'])->name('teacher.search.result');
    Route::get('/month/list', [TeacherScheduleController::class, 'result'])->name('month.list');
    Route::get('/classSearch', [TeacherClassController::class, 'search'])->name('teacher.classSearch');
    Route::get('/classSearch/{date}', [TeacherClassController::class, 'search'])->name('teacher.classSearch.date');

});

Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin', [AdminAuthController::class, 'index'])->name('admin.admin');
    Route::get('/admin/admin.teacher', [TeacherController::class, 'index'])->name('admin.admin_teacher');
    Route::resource('teachers', TeacherController::class);
    Route::get('/admin/teacher.register', [TeacherController::class, 'create'])->name('admin.teacher_register');
    Route::get('admin/notice', [NoticeController::class, 'index'])->name('admin.notice');
    Route::resource('notices', NoticeController::class);
});

