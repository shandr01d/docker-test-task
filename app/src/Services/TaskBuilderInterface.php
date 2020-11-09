<?php

namespace App\Services;

use App\Dto\TaskDto;
use App\Entity\Task;

interface TaskBuilderInterface
{
    public function build(TaskDto $taskDto): Task;
}