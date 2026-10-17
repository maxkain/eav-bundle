<?php

namespace Maxkain\EavBundle\Tag\Options;

class EavTagOptions implements TagOptionsInterface
{
    public function __construct(
        private string $tagFqcn,
        private string $attributeTagFqcn,
        private bool $multipleTags = false,
        private TagPropertyMappingInterface $propertyMapping = new TagPropertyMapping(),
        private ?string $tagKey = null
    ) {
    }

    public function __clone(): void
    {
        $this->propertyMapping = clone $this->propertyMapping;
    }

    public function getTagFqcn(): string
    {
        return $this->tagFqcn;
    }

    public function setTagFqcn(string $tagFqcn): static
    {
        $this->tagFqcn = $tagFqcn;
        return $this;
    }

    public function getTagKey(): ?string
    {
        return $this->tagKey;
    }

    public function setTagKey(?string $tagKey): static
    {
        $this->tagKey = $tagKey;
        return $this;
    }

    public function getAttributeTagFqcn(): string
    {
        return $this->attributeTagFqcn;
    }

    public function setAttributeTagFqcn(string $attributeTagFqcn): static
    {
        $this->attributeTagFqcn = $attributeTagFqcn;
        return $this;
    }

    public function isMultipleTags(): bool
    {
        return $this->multipleTags;
    }

    public function setMultipleTags(bool $multipleTags): static
    {
        $this->multipleTags = $multipleTags;
        return $this;
    }

    public function getPropertyMapping(): TagPropertyMapping
    {
        return $this->propertyMapping;
    }

    public function setPropertyMapping(TagPropertyMapping $propertyMapping): static
    {
        $this->propertyMapping = $propertyMapping;
        return $this;
    }
}
