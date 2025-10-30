<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\OrphanedEavs;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectManager;
use Maxkain\EavBundle\Options\EavOptionsInterface;
use Maxkain\EavBundle\Options\EavOptionsRegistry;

class OrphanedEavsQueryHandler implements OrphanedEavsHandlerInterface
{
    public function __construct(
        protected EavOptionsRegistry $optionsRegistry,
        protected AffectedDataQueryFactory $affectedDataQueryFactory
    ) {
    }

    public function deleteOrphanedEavs(ObjectManager $em, string $eavFqcn, AffectedData $affectedData): void
    {
        $allOptions = $this->optionsRegistry->getByEav($eavFqcn);
        $mainOptions = current($allOptions) ?? null;
        if (!$mainOptions) {
            return;
        }

        $mapping = $mainOptions->getPropertyMapping();
        $eavAlias = 'eav';
        $eavAttributePath = $eavAlias . '.' . $mapping->getAttribute();
        $eavEntityPath = $eavAlias . '.' . $mapping->getEntity();

        $qb = $this->affectedDataQueryFactory->create($em, $eavFqcn, $eavAlias, $eavAttributePath,
            $eavEntityPath, $affectedData);

        foreach ($allOptions as $options) {
            $this->addCondition($qb, $em, $options);
        }

        $qb->delete()->getQuery()->execute();
    }

    public function addCondition(QueryBuilder $qb, ObjectManager $em, EavOptionsInterface $options): void
    {
        if (!$options->getTagFqcn()) {
            return;
        }

        $mapping = $options->getPropertyMapping();
        $eavAlias = 'eav';
        $eavAttributePath = $eavAlias . '.' . $mapping->getAttribute();
        $eavEntityPath = $eavAlias . '.' . $mapping->getEntity();
        $expr = $qb->expr();

        // Attribute tag query builder

        $attributeTagAlias = 'attribute_tag' . '_' . $options->getIndex();
        $attributeTagTagPath = $attributeTagAlias . '.' . $mapping->getAttributeTagTag();
        $attributeTagAttributePath = $attributeTagAlias . '.' . $mapping->getAttributeTagAttribute();
        $attributeTagQb = $em->getRepository($options->getAttributeTagFqcn())
            ->createQueryBuilder($attributeTagAlias)
            ->select('IDENTITY(' . $attributeTagTagPath . ')')
            ->where(
                $expr->eq($attributeTagAttributePath, $eavAttributePath)
            );

        // Entity tag query builder

        $entityAlias = 'entity' . '_' . $options->getIndex();
        $entityTagAlias = 'entity_tag' . '_' . $options->getIndex();
        if ($options->isMultipleTags()) {
            $entityTagPath = $entityAlias . '.' . $mapping->getEntityTags();
        } else {
            $entityTagPath = $entityAlias . '.' . $mapping->getEntityTag();
        }

        $entityTagQb = $em->getRepository($options->getEntityFqcn())
            ->createQueryBuilder($entityAlias)
            ->select('1')
            ->join($entityTagPath, $entityTagAlias)
            ->where(
                $expr->eq($entityAlias, $eavEntityPath)
            )
            ->andWhere(
                $expr->in($entityTagAlias, $attributeTagQb->getDQL())
            );

        // Attribute forAllTags query builder

        $attributeAlias = 'attribute' . '_' . $options->getIndex();
        $attributeForAllTagsPath = $attributeAlias . '.' . $mapping->getAttributeForAllTags();
        $forAllQb = $em->getRepository($options->getAttributeFqcn())->createQueryBuilder($attributeAlias);
        $forAllQb->select('1')
            ->where($expr->eq($attributeAlias, $eavAttributePath))
            ->andWhere(
                $expr->eq($attributeForAllTagsPath, $qb->createNamedParameter(true))
            );

        // Add inner query builders to main

        $qb->andWhere(
            $expr->not($expr->exists($forAllQb)),
            $expr->not($expr->exists($entityTagQb))
        );
    }
}
