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
use App\Http\Controllers\LessonController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminScheduleController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\AdminStatusController;


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
    Route::get('status/list/{user_lesson_id}', [StatusController::class, 'show'])->name('status.list');
    Route::post('/status/absence/confirm/{userLessonId}', [StatusController::class, 'confirmAbsence'])->name('status.absence.confirm');
    Route::get('/status/makeup/{userLessonId}', [StatusController::class, 'makeupShow'])->name('status.makeup');
    Route::post('/makeup/update/{userLessonId}', [StatusController::class, 'makeupUpdate'])->name('makeup.update');
    Route::delete('/reschedule/cancel/{rescheduleId}', [StatusController::class, 'cancelReschedule'])->name('reschedule.cancel');
});

Route::prefix('teacher')->group(function () {
    Route::get('/login', [TeacherAuthController::class, 'showLoginForm'])->name('teacher.login');
    Route::post('/login', [TeacherAuthController::class, 'login'])->name('teacher.login.submit');
    Route::post('/logout', [TeacherAuthController::class, 'logout'])->name('teacher.logout');
});

Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

});

Route::middleware(['auth:teacher'])->group(function () {
    Route::get('/teacher', [TeacherAuthController::class, 'index'])->name('teacher.teacher');
    Route::get('/search',[TeacherScheduleController::class, 'showForm'])->name('teacher.search');
    Route::post('/search/result', [TeacherScheduleController::class, 'result'])->name('teacher.search.result');
    Route::get('/month/list', [TeacherScheduleController::class, 'result'])->name('teacher.month.list');
    Route::get('/classSearch', [TeacherClassController::class, 'search'])->name('teacher.classSearch');
    Route::get('/classSearch/{date}', [TeacherClassController::class, 'search'])->name('teacher.classSearch.date');
    Route::get('/class/{lesson}', [TeacherClassController::class, 'classList'])->name('teacher.class.list');
    Route::post('/status/toggle', [TeacherClassController::class, 'toggleStatus'])->name('teacher.status.toggle');
});

Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin', [AdminAuthController::class, 'index'])->name('admin.admin');
    Route::get('/admin/admin.teacher', [TeacherController::class, 'index'])->name('admin.admin_teacher');
    Route::resource('teachers', TeacherController::class);
    Route::get('/admin/teacher.register', [TeacherController::class, 'create'])->name('admin.teacher_register');
    Route::get('admin/notice', [NoticeController::class, 'index'])->name('admin.notice');
    Route::resource('notices', NoticeController::class);
    
    Route::prefix('admin/lesson')->name('admin.lesson.')->group(function () {
        Route::get('/', [LessonController::class, 'index'])->name('index');
        Route::get('/search', [LessonController::class, 'search'])->name('search');
        Route::get('/show', [LessonController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [LessonController::class, 'edit'])->name('edit'); // 編集用ルート
        Route::put('/{id}', [LessonController::class, 'update'])->name('update'); 
        Route::delete('/{id}', [LessonController::class, 'destroy'])->name('destroy');
        Route::get('/pdf', [LessonController::class, 'generatePDF'])->name('pdf');
        Route::get('/create', [LessonController::class, 'create'])->name('create');
        Route::post('/store', [LessonController::class, 'store'])->name('store');
        Route::post('/update-next-year/confirm', [LessonController::class, 'updateNextYear'])->name('updateNextYear');
        Route::post('/update-next-year/store', [LessonController::class, 'updateNextYearStore'])->name('updateNextYearStore');
    });

    Route::prefix('admin/master')->name('admin.master.')->group(function () {
        Route::get('/', [MasterController::class, 'index'])->name('index');
        Route::post('/schools', [MasterController::class, 'storeSchool'])->name('schools.store');
        Route::post('/classes', [MasterController::class, 'storeClass'])->name('classes.store');
        Route::delete('/schools/{id}', [MasterController::class, 'destroySchool'])->name('schools.destroy');
        Route::delete('/classes/{id}', [MasterController::class, 'destroyClass'])->name('classes.destroy');
    });

    Route::prefix('admin/student')->name('admin.student.')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::get('/create', [StudentController::class, 'create'])->name('create');
        Route::post('/store', [StudentController::class, 'store'])->name('store');
        Route::get('/search', [StudentController::class, 'search'])->name('search');
        Route::get('/show', [studentController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [StudentController::class, 'edit'])->name('edit'); 
        Route::put('/{id}', [StudentController::class, 'update'])->name('update'); 
        Route::delete('/{userId}/destroy-all', [StudentController::class, 'destroyAll'])->name('destroyAll');
        Route::get('/show-next-year',[StudentController::class, 'showNextYear'])->name('showNextYear');
        Route::get('/show-next-year/search', [StudentController::class, 'searchStudent'])->name('searchStudent');
        Route::post('/show-next-year/store', [StudentController::class, 'storeStudent'])->name('storeStudent');
    });

    Route::prefix('admin/schedule')->name('admin.schedule.')->group(function () {
        Route::get('/', [AdminScheduleController::class, 'index'])->name('index');
        Route::get('/list', [AdminScheduleController::class, 'show'])->name('show');
        Route::post('/update/{lessonId}', [AdminScheduleController::class, 'update'])->name('update');

    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/class', [AdminStatusController::class, 'index'])->name('class.index');
        Route::get('/status/search', [AdminStatusController::class, 'search'])->name('status.search');
        Route::post('/status/absent/{userLessonId}', [AdminStatusController::class, 'toggleAbsence'])->name('status.absent');
        Route::get('/student/{id}', [AdminStatusController::class, 'detail'])->name('student.detail');

        Route::get('/status/makeup/{userLessonId}', [AdminStatusController::class, 'makeupShow'])->name('status.makeup');
        Route::post('/makeup/update/{userLessonStatusId}', [AdminStatusController::class, 'makeupUpdate'])->name('makeup.update');
        Route::delete('/reschedule/cancel/{rescheduleId}', [AdminStatusController::class, 'cancelReschedule'])->name('reschedule.cancel');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/mails', [MailController::class, 'index'])->name('mails.index');
        Route::get('/mails/create', [MailController::class, 'create'])->name('mails.create');
        Route::get('/mails/sendTo', [MailController::class, 'search'])->name('mails.sendTo');
        Route::get('/schools/{year}', [MailController::class, 'getSchoolsByYear']);
        Route::get('/classes/{schoolId}', [MailController::class, 'getClassesBySchool']);
        Route::get('/days/{classId}', [MailController::class, 'getDaysByClass']);
        Route::post('/mails/send', [MailController::class, 'result'])->name('mails.send');
        Route::post('/mails/confirm', [MailController::class, 'confirm'])->name('mails.confirm');
        Route::post('/mails/store', [MailController::class, 'store'])->name('mails.store');

    });


});

