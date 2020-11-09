<?php

namespace App\Doctrine;

use App\Entity\Task;
use App\Entity\TaskList;

class OwnerQueryResolver implements OwnerQueryResolverInterface
{
    public function resolve(string $className): ?OwnerQueryDecoratorInterface
    {
        switch ($className){
            case TaskList::class:
                return new TaskListOwnerQueryDecorator();
                break;
            case Task::class:
                return new TaskOwnerQueryDecorator();
                break;
        }
    }
}