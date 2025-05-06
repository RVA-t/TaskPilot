<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MyTaskController extends Controller
{
public function index(Project $project)
{
    $user = Auth::user();

    $tasks = $project->tasks()
        ->where('assignee_id', $user->id)
        ->select('id', 'title', 'column_index')
        ->get();

    return Inertia::render('mytask/MyTask', [
        'project' => $project,
        'tasks' => $tasks,
        ]);
    }
}
