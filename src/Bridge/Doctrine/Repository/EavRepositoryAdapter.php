<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\Repository;

use Doctrine\Persistence\ObjectRepository;
use Maxkain\EavBundle\Bridge\Doctrine\Provider\DoctrineProvider;
use Maxkain\EavBundle\Contracts\Repository\AttributeRepositoryInterface;
use Maxkain\EavBundle\Contracts\Repository\EntityRepositoryInterface;
use Maxkain\EavBundle\Contracts\Repository\EavRepositoryInterface;
use Maxkain\EavBundle\Contracts\Repository\ValueRepositoryInterface;

final class EavRepositoryAdapter implements EavRepositoryInterface, EntityRepositoryInterface,
    AttributeRepositoryInterface, ValueRepositoryInterface
{
    public function __construct(
        protected DoctrineProvider $doctrineProvider
    ) {
    }

    public function findOneBy(string $fqcn, array $criteria, ?array $orderBy = null): ?object
    {
        return $this->getRepository($fqcn)->findOneBy($criteria, $orderBy);
    }

    public function findBy(string $fqcn, array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->getRepository($fqcn)->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findAll(string $fqcn): array
    {
        return $this->getRepository($fqcn)->findAll();
    }

    protected function getRepository(string $fqcn): ObjectRepository
    {
        return $this->doctrineProvider->getRepository($fqcn);
    }
}
