<?php


namespace App\Doctrine;


use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskListOwnerQueryDecorator implements OwnerQueryDecoratorInterface
{
    public function updateQuery(QueryBuilder $queryBuilder, UserInterface $user): void
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->andWhere(sprintf('%s.owner = :current_user', $rootAlias));
        $queryBuilder->setParameter('current_user', $user->getId());
    }
}