<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\OrphanedEavs;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectManager;

class AffectedDataQueryFactory
{
    public function create(
        ObjectManager $em,
        string $eavFqcn,
        string $eavAlias,
        string $eavAttributePath,
        string $eavEntityPath,
        ?AffectedData $affectedData
    ): QueryBuilder {
        $qb = $em->getRepository($eavFqcn)->createQueryBuilder($eavAlias);

        $expr = $qb->expr();

        $orX = $expr->orX();
        if ($affectedData?->attributes) {
            $orX->add($expr->in($eavAttributePath, ':attributes'));
            $qb->setParameter('attributes', $affectedData->attributes);
        }
        if ($affectedData?->entities) {
            $orX->add($expr->in($eavEntityPath, ':entities'));
            $qb->setParameter('entities', $affectedData->entities);
        }

        if ($orX->count()) {
            $qb->andWhere($orX);
        }

        return $qb;
    }
}
