<?php

namespace App\Actions\Admin;

use App\Models\Task;

class DeleteTaskAction
{
    public function handle(Task $task): void
    {
        $task->delete();
    }
}
