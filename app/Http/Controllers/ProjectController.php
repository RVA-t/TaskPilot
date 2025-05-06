<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    // ✅ Показываем только проекты, где пользователь — участник
    public function index(Request $request)
    {
        return Inertia::render('projects/Index', [
            'projects' => $request->user()->projects()->get(),
        ]);
    }

    // ✅ При создании проекта — добавляем пользователя как участника
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $project = $request->user()->ownedProjects()->create($validated);

        // Добавляем текущего пользователя как участника
        $project->users()->attach($request->user()->id);

        return redirect()->route('projects.index')->with('success', 'Project created!');
    }

    // ✅ Отображение участников проекта
    public function members(Project $project)
    {
        return Inertia::render('members/Index', [
            'project' => $project,
            'members' => $project->users()->get(),
        ]);
    }

    // ✅ Добавление нового участника по email
    public function addMember(Request $request, Project $project)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $project->users->contains($user->id)) {
            $project->users()->attach($user->id);
        }

        return redirect()->route('projects.members', $project->id)->with('success', 'Участник добавлен!');
    }

    public function removeMember(Project $project, User $user)
    {
        $project->users()->detach($user->id);
        return back()->with('success', 'Участник удалён.');
    }

}
