<?php

namespace App\Services;

use App\Dto\TaskUpdateDto;
use App\Entity\Task;
use App\Exceptions\TaskUpdateException;
use App\Repository\TaskListRepository;
use Symfony\Component\Security\Core\Security;

class TaskUpdater implements TaskUpdaterInterface
{
    private $taskListRepository;
    private $security;

    public function __construct(TaskListRepository $taskListRepository, Security $security)
    {
        $this->taskListRepository = $taskListRepository;
        $this->security = $security;
    }

    public function update(Task $task, TaskUpdateDto $taskDto): Task
    {
        try {
            $user = $this->security->getUser();
            if ($taskDto->dueDate) {
                $list = $this->taskListRepository->findOneOrCreateByDueDateAndOwner($taskDto->dueDate, $user);
                $task->setList($list);
            }

            $task->setTitle($taskDto->title ?? $task->getTitle());
            $task->setStatus($taskDto->status ?? $task->getStatus());

            return $task;
        } catch (TaskUpdateException $exception) {
            throw $exception;
        }
    }
}