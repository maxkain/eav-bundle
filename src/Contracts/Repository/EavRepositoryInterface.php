<?php

namespace Maxkain\EavBundle\Contracts\Repository;

interface EavRepositoryInterface
{
    public function findOneBy(string $fqcn, array $criteria, ?array $orderBy = null): ?object;
    public function findBy(string $fqcn, array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;
    public function findAll(string $fqcn): array;
}
