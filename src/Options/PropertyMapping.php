<?php

namespace Maxkain\EavBundle\Options;

class PropertyMapping implements PropertyMappingInterface
{
    public function __construct(
        private string $entity = 'entity',
        private string $attribute = 'attribute',
        private string $value = 'value',
        private string $entityId = 'id',
        private string $attributeId = 'id',
        private string $attributeName = 'name',
        private string $valueId = 'id',
        private string $valueAttribute = 'attribute',
        private string $valueTitle = 'title',
        private string $entityTag = 'category',
        private string $entityTags = 'categories',
        private string $attributeForAllTags = 'forAllTags',
        private string $attributeTagAttribute = 'attribute',
        private string $attributeTagTag = 'tag'
    ) {
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function setEntity(string $entity): static
    {
        $this->entity = $entity;
        return $this;
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

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    public function setEntityId(string $entityId): static
    {
        $this->entityId = $entityId;
        return $this;
    }

    public function getAttributeId(): string
    {
        return $this->attributeId;
    }

    public function setAttributeId(string $attributeId): static
    {
        $this->attributeId = $attributeId;
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

    public function getValueId(): string
    {
        return $this->valueId;
    }

    public function setValueId(string $valueId): static
    {
        $this->valueId = $valueId;
        return $this;
    }

    public function getValueAttribute(): string
    {
        return $this->valueAttribute;
    }

    public function setValueAttribute(string $valueAttribute): static
    {
        $this->valueAttribute = $valueAttribute;
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

    public function getAttributeForAllTags(): string
    {
        return $this->attributeForAllTags;
    }

    public function setAttributeForAllTags(string $attributeForAllTags): static
    {
        $this->attributeForAllTags = $attributeForAllTags;
        return $this;
    }
}
