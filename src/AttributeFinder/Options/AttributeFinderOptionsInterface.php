<?php

namespace Maxkain\EavBundle\AttributeFinder\Options;

interface AttributeFinderOptionsInterface
{
    public function getEntityFqcn(): string;
    public function getAttributeFqcn(): string;
    public function getPropertyMapping(): AttributeFinderPropertyMappingInterface;
}
