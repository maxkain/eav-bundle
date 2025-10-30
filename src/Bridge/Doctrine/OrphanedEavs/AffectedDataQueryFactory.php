<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\OrphanedEavs;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectManager;
use Maxkain\EavBundle\Options\EavOptionsInterface;

class AffectedDataQueryFactory
{
    public function create(
        ObjectManager $em,
        string $eavFqcn,
        string $eavAlias,
        string $eavAttributePath,
        string $eavEntityPath,
        AffectedData $affectedData
    ): QueryBuilder {
        $qb = $em->getRepository($eavFqcn)->createQueryBuilder($eavAlias);
        $expr = $qb->expr();

        return $qb->andWhere(
            $expr->orX(
                $expr->in($eavAttributePath, ':attributes'),
                $expr->in($eavEntityPath, ':entities'),
            )
        )
        ->setParameter('attributes', $affectedData->attributes)
        ->setParameter('entities', $affectedData->entities);
    }
}
