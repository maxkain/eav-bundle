<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\OrphanedEavs;

use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;
use Maxkain\EavBundle\Contracts\Entity\EavEntityInterface;
use Maxkain\EavBundle\Contracts\Entity\Tag\EavAttributeTagInterface;
use Maxkain\EavBundle\Contracts\Entity\Tag\EavAttributeWithTagsInterface;
use Maxkain\EavBundle\Contracts\Entity\Tag\EavEntityWithTagsInterface;
use Maxkain\EavBundle\Contracts\Entity\Tag\EavTagInterface;
use Maxkain\EavBundle\Options\EavOptionsRegistry;

class AffectedDataExtractor
{
    public function __construct(
        protected EavOptionsRegistry $optionsRegistry
    ) {
    }

    /**
     * @return array<string, AffectedData>
     */
    public function extract(UnitOfWork $uow): array
    {
        $data = [];

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->extractAttributes($data, $entity);
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->extractAttributes($data, $entity);
            $this->extractEntitiesWithSingularTag($data, $entity, $uow);
        }

        foreach ($uow->getScheduledCollectionUpdates() as $collection) {
            $this->extractEntitiesWithMultipleTag($data, $collection);
        }

        foreach ($uow->getScheduledCollectionDeletions() as $collection) {
            $this->extractEntitiesWithMultipleTag($data, $collection);
        }

        return $data;
    }

    protected function extractAttributes(array &$data, object $entity): void
    {
        $attribute = null;

        if ($entity instanceof EavAttributeWithTagsInterface) {
            $attribute = $entity;
        } else if ($entity instanceof EavAttributeTagInterface) {
            $attribute = $entity->getAttribute();
        }

        if ($attribute instanceof EavAttributeWithTagsInterface) {
            foreach ($this->getEavFqcnsByAttribute($attribute) as $eavFqcn) {
                $data[$eavFqcn] = $data[$eavFqcn] ?? new AffectedData();
                $data[$eavFqcn]->attributes[$attribute->getId()] = $attribute;
            }
        }
    }

    protected function extractEntitiesWithSingularTag(array &$data, object $entity, UnitOfWork $uow): void
    {
        if ($entity instanceof EavEntityWithTagsInterface) {
            $changeset = $uow->getEntityChangeSet($entity);
            foreach ($changeset as $changesetData) {
                if ($changesetData[0] instanceof EavTagInterface) {
                    foreach ($this->getEavFqcnsByEntity($entity) as $eavFqcn) {
                        $data[$eavFqcn] = $data[$eavFqcn] ?? new AffectedData();
                        $data[$eavFqcn]->entities[$entity->getId()] = $entity;
                    }
                }
            }
        }
    }

    protected function extractEntitiesWithMultipleTag(array &$data, PersistentCollection $collection): void
    {
        $entity = $collection->getOwner();
        if ($entity instanceof EavEntityWithTagsInterface) {
            foreach ($collection->getDeleteDiff() as $tag) {
                if ($tag instanceof EavTagInterface) {
                    foreach($this->getEavFqcnsByEntity($entity) as $eavFqcn) {
                        $data[$eavFqcn] = $data[$eavFqcn] ?? new AffectedData();
                        $data[$eavFqcn]->entities[$entity->getId()] = $entity;
                    }
                }
            }
        }
    }

    /**
     * @return array<string>
     */
    protected function getEavFqcnsByAttribute(EavAttributeInterface $attribute): array
    {
        $eavFqcns = [];
        $options = $this->optionsRegistry->getByAttribute(get_class($attribute));
        foreach ($options as $option) {
            $eavFqcns[$option->getEavFqcn()] = 1;
        }

        return array_keys($eavFqcns);
    }

    /**
     * @return array<string>
     */
    protected function getEavFqcnsByEntity(EavEntityInterface $entity): array
    {
        $eavFqcns = [];
        $options = $this->optionsRegistry->getByEntity(get_class($entity));
        foreach ($options as $option) {
            $eavFqcns[$option->getEavFqcn()] = 1;
        }

        return array_keys($eavFqcns);
    }
}
