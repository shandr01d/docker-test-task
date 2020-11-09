<?php

namespace App\Services;

use App\Dto\TaskUpdateDto;
use App\Entity\Task;

interface TaskUpdaterInterface
{
    public function update(Task $task, TaskUpdateDto $taskDto): Task;
}