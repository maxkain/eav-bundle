<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\OrphanedEavs;

use Doctrine\Persistence\ObjectManager;
use Maxkain\EavBundle\Bridge\Doctrine\Query\EavTagQueryFactory;
use Maxkain\EavBundle\Bridge\Doctrine\Query\QueryFieldPath;
use Maxkain\EavBundle\Options\EavOptionsResolverInterface;

class OrphanedEavsQueryHandler implements OrphanedEavsHandlerInterface
{
    public function __construct(
        protected AffectedDataQueryFactory $affectedDataQueryFactory,
        protected EavTagQueryFactory $eavTagQueryFactory,
        protected int $defaultBatchSize = 1000,
        protected ?EavOptionsResolverInterface $optionsResolver = null
    ) {
    }

    public function deleteOrphanedEavs(
        ObjectManager $em,
        string $eavFqcn,
        ?AffectedData $affectedData,
        ?int $batchSize = null,
        ?callable $qbModifier = null,
        ?EavOptionsResolverInterface $optionsResolver = null
    ): void {
        $optionsResolver ??= $this->optionsResolver;
        $options = $optionsResolver->resolve($eavFqcn);
        if (!$options->getTagOptions()) {
            return;
        }

        $batchSize ??= $this->defaultBatchSize;
        $mapping = $options->getPropertyMapping();
        $eavAlias = 'eav';
        $entityAlias = 'e';
        $eavAttributePath = $eavAlias . '.' . $mapping->getAttribute();
        $eavEntityPath = $eavAlias . '.' . $mapping->getEntity();
        $eavIdField = $em->getClassMetadata($eavFqcn)->getIdentifierFieldNames()[0];
        $eavIdPath = $eavAlias . '.' . $eavIdField;

        $baseQb = $this->affectedDataQueryFactory->create($em, $eavFqcn, $eavAlias, $eavAttributePath,
            $eavEntityPath, $affectedData);
        $baseQb
            ->select($eavIdPath)
            ->orderBy($eavIdPath)
            ->setMaxResults($batchSize);

        $basePageQb = (clone $baseQb);
        $baseQb->join($eavEntityPath, $entityAlias);
        $deleteQb = $em->getRepository($eavFqcn)->createQueryBuilder($eavAlias)->delete();

        $tagConditions = $this->eavTagQueryFactory->createTagConditions($baseQb, $entityAlias,
            new QueryFieldPath($eavAttributePath), $options);
        $hasTagConditions = $tagConditions->count() > 0;
        $tagConditions = $baseQb->expr()->not($tagConditions);

        if ($hasTagConditions) {
            $baseQb->andWhere($tagConditions);
        }

        if ($qbModifier) {
            $qbModifier($baseQb, $options);
        }

        $lastId = null;
        $haveIds = true;
        while ($haveIds) {
            $qb = clone $baseQb;
            $pageQb = clone $basePageQb;
            if ($lastId) {
                $pageQb->andWhere($eavIdPath . ' > :lastId')->setParameter('lastId', $lastId);
                $qb->andWhere($eavIdPath . ' > :lastId')->setParameter('lastId', $lastId);
            }

            $ids = $pageQb->getQuery()->getSingleColumnResult();
            $haveIds = (bool) $ids;
            if ($haveIds) {
                $lastId = $ids[count($ids) - 1];
                $eavIds = $qb->getQuery()->getSingleColumnResult();
                if ($eavIds) {
                    $idCondition = $deleteQb->expr()->in($eavIdPath, $eavIds);
                    $deleteQb->where($idCondition)->getQuery()->execute();
                }
            }
        }
    }
}
