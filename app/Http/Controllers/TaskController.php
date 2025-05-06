<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
//use App\Models\Comment;
use Illuminate\Http\Request;


class TaskController extends Controller
{
    protected $fillable = ['title', 'project_id', 'column_index', 'description', 'curator_id', 'assignee_id', 'urgent', 'deadline'];

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'column_index' => 'required|integer|min:0|max:3',
            'deadline' => 'nullable|date',
        ]);

        $task = Task::create($validated);

        return response()->json($task);
    }

    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'curator_id' => 'nullable|exists:users,id',
            'assignee_id' => 'nullable|exists:users,id',
            'urgent' => 'boolean',
            'deadline' => 'nullable|date',
            'column_index' => 'nullable|integer|min:0|max:3',
        ]);

        $task->update($data);

        return response()->json($task);
    }

    public function show(Task $task)
    {
        return response()->json(
            $task->load(['comments.author']) // загружаем комментарии и авторов
        );
    }

    public function index(Project $project)
    {
        return $project->tasks()->with(['assignee', 'curator'])->get();
    }

    public function updateColumn(Request $request, Task $task)
    {
        $task->column_index = $request->column_index;
        $task->save();

        return response()->json($task);
    }

}
