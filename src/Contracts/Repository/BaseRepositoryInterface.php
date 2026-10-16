<?php

namespace Maxkain\EavBundle\Contracts\Repository;

/**
 * @template T of object
 */
interface BaseRepositoryInterface
{
    /**
     * @return T|null
     */
    public function findOneBy(string $fqcn, array $criteria, ?array $orderBy = null): ?object;

    /**
     * @return array<T>
     */
    public function findBy(string $fqcn, array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    /**
     * @return array<T>
     */
    public function findAll(string $fqcn): array;
}
