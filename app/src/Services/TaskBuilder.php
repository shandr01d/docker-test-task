<?php

namespace App\Services;

use App\Dto\TaskDto;
use App\Entity\Task;
use App\Enum\TaskStatus;
use App\Exceptions\TaskCreationException;
use App\Repository\TaskListRepository;
use Symfony\Component\Security\Core\Security;

class TaskBuilder implements TaskBuilderInterface
{
    private $taskListRepository;
    private $security;

    public function __construct(TaskListRepository $taskListRepository, Security $security)
    {
        $this->taskListRepository = $taskListRepository;
        $this->security = $security;
    }

    public function build(TaskDto $taskDto): Task
    {
        try {
            $user = $this->security->getUser();
            $list = $this->taskListRepository->findOneOrCreateByDueDateAndOwner($taskDto->dueDate, $user);

            $task = new Task();
            $task->setTitle($taskDto->title);
            $task->setStatus(TaskStatus::CREATED);
            $task->setList($list);
            $list->setOwner($user);

            return $task;
        } catch (TaskCreationException $exception){
            throw $exception;
        }
    }
}