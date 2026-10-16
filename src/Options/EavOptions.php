<?php

namespace Maxkain\EavBundle\Options;

use Maxkain\EavBundle\Tag\Options\TagOptionsInterface;

class EavOptions implements EavOptionsInterface, SetIndexInterface
{
    private int $index = 0;

    /**
     * @param ?string $entityInputType One of gettype() values or null for any type.
     * @param ?string $attributeInputType One of gettype() values or null for any type.
     * @param ?string $valueInputType One of gettype() values or null for any type.
     * @param array<TagOptionsInterface> $tagOptions
     */
    public function __construct(
        private string $eavFqcn,
        private string $entityFqcn,
        private string $attributeFqcn,
        private ?string $valueFqcn = null,
        private bool $multiple = false,
        private ?string $entityInputType = null,
        private ?string $attributeInputType = null,
        private ?string $valueInputType = null,
        private bool $convertItemsToArrays = false,
        private bool $ignoreInputEmptyValue = true,
        private array $tagOptions = [],
        private bool $orTagLogic = true,
        private PropertyMapping $propertyMapping = new PropertyMapping(),
        private ReversePropertyMapping $reversePropertyMapping = new ReversePropertyMapping()
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

    /**
     * @return array<TagOptionsInterface>
     */
    public function getTagOptions(): array
    {
        return $this->tagOptions;
    }

    /**
     * @param array<TagOptionsInterface> $tagOptions
     */
    public function setTagOptions(array $tagOptions): static
    {
        $this->tagOptions = $tagOptions;
        return $this;
    }

    public function isOrTagLogic(): bool
    {
        return $this->orTagLogic;
    }

    public function setOrTagLogic(bool $orTagLogic): static
    {
        $this->orTagLogic = $orTagLogic;
        return $this;
    }

    public function getPropertyMapping(): PropertyMapping
    {
        return $this->propertyMapping;
    }

    public function setPropertyMapping(PropertyMapping $propertyMapping): static
    {
        $this->propertyMapping = $propertyMapping;
        return $this;
    }

    public function getReversePropertyMapping(): ReversePropertyMapping
    {
        return $this->reversePropertyMapping;
    }

    public function setReversePropertyMapping(ReversePropertyMapping $reversePropertyMapping): static
    {
        $this->reversePropertyMapping = $reversePropertyMapping;
        return $this;
    }
}
