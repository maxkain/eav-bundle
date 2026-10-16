<?php

namespace Maxkain\EavBundle\Tag\Options;

use Maxkain\EavBundle\Tag\Options\TagPropertyMappingInterface;

class TagPropertyMapping implements TagPropertyMappingInterface
{
    public function __construct(
        private string $entityTag = 'category',
        private string $entityTags = 'categories',
        private string $attributeForAllTags = 'forAllTags',
        private string $attributeTagAttribute = 'attribute',
        private string $attributeTagTag = 'tag'
    ) {
    }

    public function getEntityTag(): string
    {
        return $this->entityTag;
    }

    public function setEntityTag(string $entityTag): static
    {
        $this->entityTag = $entityTag;
        return $this;
    }

    public function getEntityTags(): string
    {
        return $this->entityTags;
    }

    public function setEntityTags(string $entityTags): static
    {
        $this->entityTags = $entityTags;
        return $this;
    }

    public function getAttributeForAllTags(): string
    {
        return $this->attributeForAllTags;
    }

    public function setAttributeForAllTags(string $attributeForAllTags): static
    {
        $this->attributeForAllTags = $attributeForAllTags;
        return $this;
    }

    public function getAttributeTagAttribute(): string
    {
        return $this->attributeTagAttribute;
    }

    public function setAttributeTagAttribute(string $attributeTagAttribute): static
    {
        $this->attributeTagAttribute = $attributeTagAttribute;
        return $this;
    }

    public function getAttributeTagTag(): string
    {
        return $this->attributeTagTag;
    }

    public function setAttributeTagTag(string $attributeTagTag): static
    {
        $this->attributeTagTag = $attributeTagTag;
        return $this;
    }
}
