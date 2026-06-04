<?php

use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\DeanController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificationController;

Route::middleware('guest')->group(function () {
    Route::get('/login',     [LoginController::class,    'showLoginForm'])->name('login');
    Route::post('/login',    [LoginController::class,    'login']);
    Route::get('/register',  [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LogoutController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');
Route::get('/', fn() => redirect()->route('login'));

Route::middleware('auth')->group(function () {

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    // Студент
    Route::prefix('student')->name('student.')->middleware('role:student,teacher,dean,admin')->group(function () {
        Route::get('/dashboard',      [StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/debts',          [StudentController::class, 'debts'])->name('debts');
        Route::get('/retakes',        [StudentController::class, 'retakes'])->name('retakes');
        Route::get('/request-role',   [StudentController::class, 'requestTeacherRole'])->name('request-role');
        Route::post('/request-role',  [StudentController::class, 'submitTeacherRoleRequest'])->name('request-role.store');
    });

    // Преподаватель
    Route::prefix('teacher')->name('teacher.')->middleware('role:teacher,dean,admin')->group(function () {
        Route::get('/dashboard',               [TeacherController::class, 'dashboard'])->name('dashboard');
        Route::get('/debts',                   [TeacherController::class, 'debts'])->name('debts');
        Route::get('/debts/create',            [TeacherController::class, 'createDebt'])->name('debts.create');
        Route::post('/debts',                  [TeacherController::class, 'storeDebt'])->name('debts.store');
        Route::post('/debts/{debt}/close',     [TeacherController::class, 'closeDebt'])->name('debts.close');
        Route::get('/retakes',                 [TeacherController::class, 'retakes'])->name('retakes');
        Route::get('/retakes/{retake}/results',[TeacherController::class, 'retakeResults'])->name('retakes.results');
        Route::post('/retakes/{retake}/results',[TeacherController::class, 'saveRetakeResults'])->name('retakes.results.save');
        Route::get('/requests',                [TeacherController::class, 'requests'])->name('requests');
        Route::post('/requests',               [TeacherController::class, 'storeRequest'])->name('requests.store');
    });

    // Деканат
    Route::prefix('dean')->name('dean.')->middleware('role:dean,admin')->group(function () {
        Route::get('/dashboard',                      [DeanController::class, 'dashboard'])->name('dashboard');
        Route::get('/debts',                          [DeanController::class, 'debts'])->name('debts');
        Route::get('/retakes',                        [DeanController::class, 'retakes'])->name('retakes.index');
        Route::get('/retakes/create',                 [DeanController::class, 'createRetake'])->name('retakes.create');
        Route::post('/retakes',                       [DeanController::class, 'storeRetake'])->name('retakes.store');
        Route::get('/requests',                       [DeanController::class, 'requests'])->name('requests');
        Route::post('/requests/{request}/review',     [DeanController::class, 'reviewRequest'])->name('requests.review');
        Route::get('/reports',                        [DeanController::class, 'reports'])->name('reports');
        Route::get('/reports/export',                 [DeanController::class, 'exportCsv'])->name('reports.export');
        Route::get('/import',                         [ImportController::class, 'showForm'])->name('import');
        Route::post('/import/students',               [ImportController::class, 'importStudents'])->name('import.students');
        Route::post('/import/debts',                  [ImportController::class, 'importDebts'])->name('import.debts');
        Route::get('/import/template/{type}', [ImportController::class, 'downloadTemplate'])->name('import.template');


    });

    // Администратор
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/users',                              [AdminController::class, 'users'])->name('users');
        Route::post('/users/{user}/toggle-dean',          [AdminController::class, 'toggleDean'])->name('users.toggle-dean');
        Route::post('/users/{user}/toggle-teacher',       [AdminController::class, 'toggleTeacher'])->name('users.toggle-teacher');
        Route::get('/disciplines',                        [AdminController::class, 'disciplines'])->name('disciplines');
        Route::post('/disciplines',                       [AdminController::class, 'storeDiscipline'])->name('disciplines.store');
        Route::delete('/disciplines/{discipline}',        [AdminController::class, 'deleteDiscipline'])->name('disciplines.delete');
        Route::get('/role-requests',                      [AdminController::class, 'roleRequests'])->name('role-requests');
        Route::post('/role-requests/{roleRequest}/review',[AdminController::class, 'reviewRoleRequest'])->name('role-requests.review');
    });
});