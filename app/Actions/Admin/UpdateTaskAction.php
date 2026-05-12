<?php

namespace App\Actions\Admin;

use App\Models\Task;

class UpdateTaskAction
{
    /**
     * @param  array{name: string, amount: float}  $data
     */
    public function handle(Task $task, array $data): Task
    {
        $task->update($data);

        return $task;
    }
}
