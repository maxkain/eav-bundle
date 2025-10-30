<?php

namespace Maxkain\EavBundle\Options;

class ReversePropertyMapping implements ReversePropertyMappingInterface
{
    public function __construct(
        private string $attribute = 'attribute',
        private string $attributeName = 'attribute_name',
        private string $values = 'values',
        private string $value = 'value',
        private string $valueTitles = 'value_titles',
        private string $valueTitle = 'value_title',
    ) {
    }

    public function getAttribute(): string
    {
        return $this->attribute;
    }

    public function setAttribute(string $attribute): static
    {
        $this->attribute = $attribute;
        return $this;
    }

    public function getAttributeName(): string
    {
        return $this->attributeName;
    }

    public function setAttributeName(string $attributeName): static
    {
        $this->attributeName = $attributeName;
        return $this;
    }

    public function getValues(): string
    {
        return $this->values;
    }

    public function setValues(string $values): static
    {
        $this->values = $values;
        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function getValueTitles(): string
    {
        return $this->valueTitles;
    }

    public function setValueTitles(string $valueTitles): static
    {
        $this->valueTitles = $valueTitles;
        return $this;
    }

    public function getValueTitle(): string
    {
        return $this->valueTitle;
    }

    public function setValueTitle(string $valueTitle): static
    {
        $this->valueTitle = $valueTitle;
        return $this;
    }
}
