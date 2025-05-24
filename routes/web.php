<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectCalendarController;
use App\Http\Controllers\MyTaskController;
use App\Http\Controllers\NotificationController;


Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/dashboard/{project}', [DashboardController::class, 'show'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');

    Route::get('/projects/{project}/members', [ProjectController::class, 'members'])->name('projects.members');
    Route::post('/projects/{project}/members', [ProjectController::class, 'addMember'])->name('projects.members.add');
    Route::delete('/projects/{project}/members/{user}', [ProjectController::class, 'removeMember'])->name('projects.members.remove');

    Route::get('/projects/{project}/calendar', [ProjectCalendarController::class, 'show'])->name('projects.calendar');

    Route::get('/projects/{project}/my-tasks', [MyTaskController::class, 'index'])->name('mytask.index');

    Route::get('/projects/{project}/notify', [NotificationController::class, 'showForm'])->name('projects.notify.form');
    Route::post('/projects/{project}/notify', [NotificationController::class, 'send'])->name('projects.notify.send');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
