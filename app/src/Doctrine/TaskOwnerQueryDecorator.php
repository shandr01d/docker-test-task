<?php

namespace App\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskOwnerQueryDecorator implements OwnerQueryDecoratorInterface
{
    public function updateQuery(QueryBuilder $queryBuilder, UserInterface $user): void
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->innerJoin($rootAlias.'.list', 'l', 'WITH',  $rootAlias.'.list = l.id');
        $queryBuilder->andWhere('l.owner = :current_user');
        $queryBuilder->setParameter('current_user', $user->getId());
    }
}