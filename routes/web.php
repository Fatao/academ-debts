<?php

use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FreelancerController;
use App\Http\Controllers\JobgiverController;
use App\Http\Controllers\ModeratorController;
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

    // Фрилансер
    Route::prefix('freelancer')->name('freelancer.')->middleware('role:freelancer,jobgiver,moderator,admin')->group(function () {
        Route::get('/dashboard',      [FreelancerController::class, 'dashboard'])->name('dashboard');
        Route::get('/debts',          [FreelancerController::class, 'debts'])->name('debts');
        Route::get('/retakes',        [FreelancerController::class, 'retakes'])->name('retakes');
        Route::get('/request-role',   [FreelancerController::class, 'requestJobgiverRole'])->name('request-role');
        Route::post('/request-role',  [FreelancerController::class, 'submitJobgiverRoleRequest'])->name('request-role.store');
    });

    // заказчик
    Route::prefix('jobgiver')->name('jobgiver.')->middleware('role:jobgiver,moderator,admin')->group(function () {
        Route::get('/dashboard',               [JobgiverController::class, 'dashboard'])->name('dashboard');
        Route::get('/debts',                   [JobgiverController::class, 'debts'])->name('debts');
        Route::get('/debts/create',            [JobgiverController::class, 'createDebt'])->name('debts.create');
        Route::post('/debts',                  [JobgiverController::class, 'storeDebt'])->name('debts.store');
        Route::post('/debts/{debt}/close',     [JobgiverController::class, 'closeDebt'])->name('debts.close');
        Route::get('/retakes',                 [JobgiverController::class, 'retakes'])->name('retakes');
        Route::get('/retakes/{retake}/results',[JobgiverController::class, 'retakeResults'])->name('retakes.results');
        Route::post('/retakes/{retake}/results',[JobgiverController::class, 'saveRetakeResults'])->name('retakes.results.save');
        Route::get('/requests',                [JobgiverController::class, 'requests'])->name('requests');
        Route::post('/requests',               [JobgiverController::class, 'storeRequest'])->name('requests.store');
    });

    // модератор
    Route::prefix('moderator')->name('moderator.')->middleware('role:moderator,admin')->group(function () {
        Route::get('/dashboard',                      [ModeratorController::class, 'dashboard'])->name('dashboard');
        Route::get('/debts',                          [ModeratorController::class, 'debts'])->name('debts');
        Route::get('/retakes',                        [ModeratorController::class, 'retakes'])->name('retakes.index');
        Route::get('/retakes/create',                 [ModeratorController::class, 'createRetake'])->name('retakes.create');
        Route::post('/retakes',                       [ModeratorController::class, 'storeRetake'])->name('retakes.store');
        Route::get('/requests',                       [ModeratorController::class, 'requests'])->name('requests');
        Route::post('/requests/{request}/review',     [ModeratorController::class, 'reviewRequest'])->name('requests.review');
        Route::get('/reports',                        [ModeratorController::class, 'reports'])->name('reports');
        Route::get('/reports/export',                 [ModeratorController::class, 'exportCsv'])->name('reports.export');
        Route::get('/import',                         [ImportController::class, 'showForm'])->name('import');
        Route::post('/import/freelancers',               [ImportController::class, 'importFreelancers'])->name('import.freelancers');
        Route::post('/import/debts',                  [ImportController::class, 'importDebts'])->name('import.debts');
        Route::get('/import/template/{type}', [ImportController::class, 'downloadTemplate'])->name('import.template');


    });

    // Администратор
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/users',                              [AdminController::class, 'users'])->name('users');
        Route::post('/users/{user}/toggle-moderator',          [AdminController::class, 'toggleModerator'])->name('users.toggle-moderator');
        Route::post('/users/{user}/toggle-jobgiver',       [AdminController::class, 'toggleJobgiver'])->name('users.toggle-jobgiver');
        Route::get('/disciplines',                        [AdminController::class, 'disciplines'])->name('disciplines');
        Route::post('/disciplines',                       [AdminController::class, 'storeDiscipline'])->name('disciplines.store');
        Route::delete('/disciplines/{discipline}',        [AdminController::class, 'deleteDiscipline'])->name('disciplines.delete');
        Route::get('/role-requests',                      [AdminController::class, 'roleRequests'])->name('role-requests');
        Route::post('/role-requests/{roleRequest}/review',[AdminController::class, 'reviewRoleRequest'])->name('role-requests.review');
    });
});
