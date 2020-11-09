<?php

namespace App\Doctrine;

interface OwnerQueryResolverInterface
{
    public function resolve(string $className): ?OwnerQueryDecoratorInterface;
}