<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommentController;



Route::middleware(['web', 'auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/projects/{project}/tasks', [TaskController::class, 'index']);


Route::post('/tasks', [TaskController::class, 'store']);
Route::patch('/tasks/{task}', [TaskController::class, 'update']);
Route::get('/tasks/{task}', [TaskController::class, 'show']);
Route::patch('/tasks/{task}/column', [TaskController::class, 'updateColumn']);

Route::middleware(['web', 'auth:sanctum'])->group(function () {
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store']);
});
