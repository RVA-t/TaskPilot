<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Task;


class DashboardController extends Controller
{
    public function show(Project $project)
    {
        session(['current_project_id' => $project->id]);

        $tasks = Task::with(['comments.author'])
            ->where('project_id', $project->id)
            ->get();

        return inertia('Dashboard/Show', [
            'project' => $project,
            'users' => User::select('id', 'name')->get(),
            'tasks' => $tasks,
        ]);
    }
}
