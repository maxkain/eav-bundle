<?php

namespace Maxkain\EavBundle\Options;

class EavOptions implements EavOptionsInterface, SetIndexInterface
{
    private int $index = 0;

    /**
     * @param ?string $entityInputType One of gettype() values or null for any type.
     * @param ?string $attributeInputType One of gettype() values or null for any type.
     * @param ?string $valueInputType One of gettype() values or null for any type.
     */
    public function __construct(
        private string $eavFqcn,
        private string $entityFqcn,
        private string $attributeFqcn,
        private ?string $valueFqcn = null,
        private bool $multiple = false,
        private ?string $tagFqcn = null,
        private ?string $tagKey = null,
        private ?string $attributeTagFqcn = null,
        private bool $multipleTags = false,
        private ?string $entityInputType = null,
        private ?string $attributeInputType = null,
        private ?string $valueInputType = null,
        private bool $convertItemsToArrays = false,
        private bool $ignoreInputEmptyValue = true,
        private PropertyMappingInterface $propertyMapping = new PropertyMapping(),
        private ReversePropertyMappingInterface $reversePropertyMapping = new ReversePropertyMapping()
    ) {
    }

    public function __clone(): void
    {
        $this->propertyMapping = clone $this->propertyMapping;
        $this->reversePropertyMapping = clone $this->reversePropertyMapping;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function setIndex(int $index): static
    {
        $this->index = $index;
        return $this;
    }

    public function getEavFqcn(): string
    {
        return $this->eavFqcn;
    }

    public function setEavFqcn(string $eavFqcn): static
    {
        $this->eavFqcn = $eavFqcn;
        return $this;
    }

    public function getEntityFqcn(): string
    {
        return $this->entityFqcn;
    }

    public function setEntityFqcn(string $entityFqcn): static
    {
        $this->entityFqcn = $entityFqcn;
        return $this;
    }

    public function getAttributeFqcn(): string
    {
        return $this->attributeFqcn;
    }

    public function setAttributeFqcn(string $attributeFqcn): static
    {
        $this->attributeFqcn = $attributeFqcn;
        return $this;
    }

    public function getValueFqcn(): ?string
    {
        return $this->valueFqcn;
    }

    public function setValueFqcn(?string $valueFqcn): static
    {
        $this->valueFqcn = $valueFqcn;
        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function setMultiple(bool $multiple): static
    {
        $this->multiple = $multiple;
        return $this;
    }

    public function getTagFqcn(): ?string
    {
        return $this->tagFqcn;
    }

    public function setTagFqcn(?string $tagFqcn): static
    {
        $this->tagFqcn = $tagFqcn;
        return $this;
    }

    public function getTagKey(): ?string
    {
        return $this->tagKey ?? $this->tagFqcn;
    }

    public function setTagKey(?string $tagKey): static
    {
        $this->tagKey = $tagKey;
        return $this;
    }

    public function getAttributeTagFqcn(): ?string
    {
        return $this->attributeTagFqcn;
    }

    public function setAttributeTagFqcn(?string $attributeTagFqcn): static
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

    public function getEntityInputType(): ?string
    {
        return $this->entityInputType;
    }

    public function setEntityInputType(?string $entityInputType): static
    {
        $this->entityInputType = $entityInputType;
        return $this;
    }

    public function getAttributeInputType(): ?string
    {
        return $this->attributeInputType;
    }

    public function setAttributeInputType(?string $attributeInputType): static
    {
        $this->attributeInputType = $attributeInputType;
        return $this;
    }

    public function getValueInputType(): ?string
    {
        return $this->valueInputType;
    }

    public function setValueInputType(?string $valueInputType): static
    {
        $this->valueInputType = $valueInputType;
        return $this;
    }

    public function isConvertItemsToArrays(): bool
    {
        return $this->convertItemsToArrays;
    }

    public function setConvertItemsToArrays(bool $convertItemsToArrays): static
    {
        $this->convertItemsToArrays = $convertItemsToArrays;
        return $this;
    }

    public function isIgnoreInputEmptyValue(): bool
    {
        return $this->ignoreInputEmptyValue;
    }

    public function setIgnoreInputEmptyValue(bool $ignoreInputEmptyValue): static
    {
        $this->ignoreInputEmptyValue = $ignoreInputEmptyValue;
        return $this;
    }

    public function getPropertyMapping(): PropertyMappingInterface
    {
        return $this->propertyMapping;
    }

    public function setPropertyMapping(PropertyMappingInterface $propertyMapping): static
    {
        $this->propertyMapping = $propertyMapping;
        return $this;
    }

    public function getReversePropertyMapping(): ReversePropertyMappingInterface
    {
        return $this->reversePropertyMapping;
    }

    public function setReversePropertyMapping(ReversePropertyMappingInterface $reversePropertyMapping): static
    {
        $this->reversePropertyMapping = $reversePropertyMapping;
        return $this;
    }
}
