<?php

namespace Maxkain\EavBundle\AttributeFinder;

use Maxkain\EavBundle\AttributeFinder\Options\AttributeFinderOptionsInterface;
use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;
use Maxkain\EavBundle\Contracts\Entity\EavInterface;

interface EavAttributeFinderInterface
{
    /**
     * @param EavInterface|scalar $entity
     * @return array<EavAttributeInterface>
     */
    public function findAllowed(mixed $entity, AttributeFinderOptionsInterface $options): array;
}
