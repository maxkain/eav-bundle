<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\OrphanedEavs;

use Doctrine\Persistence\ObjectManager;
use Maxkain\EavBundle\Attribute\AttributeChecker;
use Maxkain\EavBundle\Contracts\Entity\EavInterface;
use Maxkain\EavBundle\Options\EavOptionsRegistry;

class OrphanedEavsObjectHandler implements OrphanedEavsHandlerInterface
{
    public function __construct(
        protected EavOptionsRegistry $optionsRegistry,
        protected AttributeChecker $attributeChecker,
        protected AffectedDataQueryFactory $affectedDataQueryFactory,
        protected int $batchSize = 100
    ) {
    }

    public function deleteOrphanedEavs(ObjectManager $em, string $eavFqcn, AffectedData $affectedData): void
    {
        $options = current($this->optionsRegistry->getByEav($eavFqcn)) ?? null;

        if (!$options?->getTagFqcn()) {
            return;
        }

        $mapping = $options->getPropertyMapping();
        $eavAlias = 'eav';
        $eavAttributePath = $eavAlias . '.' . $mapping->getAttribute();
        $eavEntityPath = $eavAlias . '.' . $mapping->getEntity();

        $qb = $this->affectedDataQueryFactory->create($em, $eavFqcn, $eavAlias, $eavAttributePath,
            $eavEntityPath, $affectedData);

        $i = 0;
        foreach ($qb->getQuery()->toIterable() as $eav) {
            if ($eav instanceof EavInterface) {
                $entity = $eav->getEntity();
                if (!$this->attributeChecker->check($entity, $eav->getAttribute())) {
                    $em->remove($eav);
                }
            }

            $i++;
            if ($i % $this->batchSize == 0) {
                $em->flush();
                $em->clear();
            }
        }

        $em->flush();
        $em->clear();
    }
}
