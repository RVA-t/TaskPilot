<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    public function view(User $user, Project $project)
    {
        return $project->users->contains($user->id);
    }

    public function update(User $user, Project $project)
    {
        // Например, только создатель может обновлять или добавлять участников
        return $project->creator_id === $user->id;
    }
}
