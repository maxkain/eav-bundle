<?php

namespace Maxkain\EavBundle\Attribute;

use Maxkain\EavBundle\Contracts\Entity\Tag\EavAttributeWithTagsInterface;
use Maxkain\EavBundle\Contracts\Entity\Tag\EavEntityWithTagsInterface;
use Maxkain\EavBundle\Contracts\Repository\EavRepositoryInterface;
use Maxkain\EavBundle\Inverter\Options\InverterOptionsInterface;
use Maxkain\EavBundle\Options\TagOptionsInterface;

class AttributeFinder
{
    public function __construct(
        protected EavRepositoryInterface $repository,
        protected AttributeChecker $attributeChecker,
    ) {
    }

    public function findAllowed(mixed $entity, InverterOptionsInterface $options): array
    {
        $repository = $this->repository;
        $mapping = $options->getPropertyMapping();
        $entity = is_object($entity)
            ? $entity
            : $repository->findOneBy($options->getEntityFqcn(), [$mapping->getEntityId() => $entity]);

        $attributes = $repository->findAll($options->getAttributeFqcn());
        $resultAttributes = [];

        if ($options instanceof TagOptionsInterface && $entity instanceof EavEntityWithTagsInterface) {
            foreach ($attributes as $attribute) {
                if ($this->attributeChecker->check($entity, $attribute)) {
                    $resultAttributes[] = $attribute;
                }
            }

            return $resultAttributes;
        }

        return $attributes;
    }
}
