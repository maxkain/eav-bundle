<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\OrphanedEavs;

use Doctrine\Persistence\ObjectManager;
use Maxkain\EavBundle\AttributeChecker\AttributeChecker;
use Maxkain\EavBundle\Contracts\Entity\EavInterface;
use Maxkain\EavBundle\Options\EavOptionsRegistry;
use Maxkain\EavBundle\Options\EavOptionsResolverInterface;

class OrphanedEavsObjectHandler implements OrphanedEavsHandlerInterface
{
    public function __construct(
        protected AttributeChecker $attributeChecker,
        protected AffectedDataQueryFactory $affectedDataQueryFactory,
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
        $eavAttributePath = $eavAlias . '.' . $mapping->getAttribute();
        $eavEntityPath = $eavAlias . '.' . $mapping->getEntity();

        $lastId = null;
        $end = false;
        $eavIdField = $em->getClassMetadata($eavFqcn)->getIdentifierFieldNames()[0];
        $eavIdPath = $eavAlias . '.' . $eavIdField;
        $qbBase = $this->affectedDataQueryFactory->create($em, $eavFqcn, $eavAlias, $eavAttributePath,
            $eavEntityPath, $affectedData)->orderBy($eavIdPath)->setMaxResults($batchSize);
        if ($qbModifier) {
            $qbModifier($qbBase, $options);
        }

        while (!$end) {
            $qb = clone $qbBase;
            if ($lastId) {
                $qb->andWhere($eavAlias . ' > :lastId')->setParameter('lastId', $lastId);
            }

            $end = true;
            foreach ($qb->getQuery()->toIterable() as $eav) {
                if ($eav instanceof EavInterface) {
                    $end = false;
                    $lastId = $eav->getId();
                    $entity = $eav->getEntity();
                    if (!$this->attributeChecker->check($entity, $eav->getAttribute(), $options)) {
                        $em->remove($eav);
                    }
                }
            }

            $em->flush();
            $em->clear();
        }
    }
}
