<?php

namespace App\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\User\UserInterface;

interface OwnerQueryDecoratorInterface
{
    public function updateQuery(QueryBuilder $queryBuilder, UserInterface $user): void;
}