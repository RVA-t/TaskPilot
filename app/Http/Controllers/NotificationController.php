<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function showForm(Project $project)
    {
        return Inertia::render('notify/TelegramNotify', [
            'project' => $project,
            'tasks' => $project->tasks()->select('id', 'title', 'deadline')->get(),
            'members' => $project->members()->select('users.id', 'users.name', 'users.telegram_chat_id')->get(),
        ]);
    }

    public function send(Request $request, Project $project)
    {
        $data = $request->validate([
            'taskIds' => 'required|array',
            'userIds' => 'required|array',
            'message' => 'nullable|string',
        ]);

        $tasks = Task::whereIn('id', $data['taskIds'])->get();
        $users = User::whereIn('id', $data['userIds'])->whereNotNull('telegram_chat_id')->get();

        $botToken = env('TELEGRAM_BOT_TOKEN');

        foreach ($users as $user) {
            foreach ($tasks as $task) {
                $text = "📝 *{$task->title}*\n"
                    . "📅 Дедлайн: " . ($task->deadline ? Carbon::parse($task->deadline)->format('d.m.Y') : 'Не указан') . "\n"
                    . "🔗 [Открыть задачу](http://localhost:8000/dashboard/{$project->id})";

                if (!empty($data['message'])) {
                    $text .= "\n💬 *Доп. сообщение:* {$data['message']}";
                }

                try {
                    Http::withOptions(['verify' => false])
                        ->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                            'chat_id' => $user->telegram_chat_id,
                            'text' => $text,
                            'parse_mode' => 'Markdown',
                            'disable_web_page_preview' => true,
                        ]);
                } catch (\Exception $e) {
                    \Log::error("Ошибка при отправке сообщения Telegram: " . $e->getMessage());
                    continue;
                }
            }
        }

        return redirect()->back()->with('success', 'Уведомления отправлены.');
    }
}
