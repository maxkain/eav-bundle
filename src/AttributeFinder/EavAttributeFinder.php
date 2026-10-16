<?php

namespace Maxkain\EavBundle\AttributeFinder;

use Maxkain\EavBundle\AttributeChecker\AttributeChecker;
use Maxkain\EavBundle\AttributeFinder\Options\AttributeFinderOptionsInterface;
use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;
use Maxkain\EavBundle\Contracts\Entity\EavInterface;
use Maxkain\EavBundle\Contracts\Repository\AttributeRepositoryInterface;
use Maxkain\EavBundle\Contracts\Repository\EntityRepositoryInterface;

class EavAttributeFinder implements EavAttributeFinderInterface
{
    public function __construct(
        protected AttributeChecker $attributeChecker,
        protected EntityRepositoryInterface $entityRepository,
        protected AttributeRepositoryInterface $attributeRepository
    ) {
    }

    /**
     * @param EavInterface|scalar $entity
     * @return array<EavAttributeInterface>
     */
    public function findAllowed(mixed $entity, AttributeFinderOptionsInterface $options): array
    {
        $mapping = $options->getPropertyMapping();
        $entity = is_object($entity)
            ? $entity
            : $this->entityRepository->findOneBy($options->getEntityFqcn(), [$mapping->getEntityId() => $entity]);
        $attributes = $this->attributeRepository->findAll($options->getAttributeFqcn());
        $resultAttributes = [];

        foreach ($attributes as $attribute) {
            if ($this->attributeChecker->check($entity, $attribute, $options)) {
                $resultAttributes[] = $attribute;
            }
        }

        return $resultAttributes;
    }
}
