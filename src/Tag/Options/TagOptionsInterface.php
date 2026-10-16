<?php

namespace Maxkain\EavBundle\Tag\Options;

interface TagOptionsInterface
{
    public function getTagFqcn(): string;
    public function getAttributeTagFqcn(): string;
    public function isMultipleTags(): bool;
    public function getPropertyMapping(): TagPropertyMappingInterface;
}
