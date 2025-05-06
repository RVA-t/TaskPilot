<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectCalendarController extends Controller
{
    public function show(Project $project)
    {
        $tasks = $project->tasks()
            ->whereNotNull('deadline')
            ->select('id', 'title', 'deadline', 'column_index as status')
            ->get();

        return Inertia::render('calendar/Calendar', [
            'project' => $project,
            'tasks' => $tasks,
            'currentProjectId' => $project->id,
        ]);
    }
}
