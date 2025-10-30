<?php

namespace Maxkain\EavBundle\Bridge\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Maxkain\EavBundle\Contracts\Repository\EavRepositoryInterface;

final class EavRepositoryAdapter implements EavRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function findOneBy(string $fqcn, array $criteria, ?array $orderBy = null): ?object
    {
        return $this->em->getRepository($fqcn)->findOneBy($criteria, $orderBy);
    }

    public function findBy(string $fqcn, array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->em->getRepository($fqcn)->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findAll(string $fqcn): array
    {
        return $this->em->getRepository($fqcn)->findAll();
    }
}
