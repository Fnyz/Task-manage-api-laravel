<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

#[Signature('tasks:check-overdue-tasks')]
#[Description('Command description')]
class CheckOverdueTasks extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $overdueTasks = Task::where('is_completed', false)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->with('user')
            ->get();

        $this->info("Found {$overdueTasks->count()} overdue task(s).");

        foreach ($overdueTasks as $task) {
            Log::warning('Overdue task detected', [
                'task_id' => $task->id,
                'title' => $task->title,
                'user' => $task->user->email,
                'due_date' => $task->due_date,
            ]);
        }
    }
}
