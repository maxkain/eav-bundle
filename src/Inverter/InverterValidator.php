<?php

namespace Maxkain\EavBundle\Inverter;

use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;
use Maxkain\EavBundle\Contracts\Entity\EavInterface;
use Maxkain\EavBundle\Contracts\Entity\EavValueInterface;
use Maxkain\EavBundle\Inverter\Options\InverterOptionsInterface;

class InverterValidator
{
    protected array $duplicatedAttributes;

    public function __construct(
        protected InverterViolationFactory $inverterViolationFactory,
    ) {
    }

    public function reset(): void
    {
        $this->duplicatedAttributes = [];
    }

    /**
     * @return array<EavInverterViolation>
     */
    public function validateItem(
        mixed $entity,
        int $itemIndex,
        mixed $attribute,
        mixed $inputValue,
        InverterOptionsInterface $options
    ): array {
        $violations = [];
        $reversMapping = $options->getReversePropertyMapping();
        $attributeProperty = $reversMapping->getAttribute();

        $entityId = $entity instanceof EavInterface ? $entity->getId() : $entity;
        $attributeId = $entity instanceof EavAttributeInterface ? $attribute->getId() : $attribute;

        $violations = array_merge($violations, $this->checkAttributeDuplicates($attributeId, $attributeProperty, $itemIndex));

        $attributePath = [$itemIndex, $attributeProperty];

        $entityType = $options->getEntityInputType();
        if ($entityType && gettype($entityId) != $entityType) {
            $violations[] = $this->createEntityTypeViolation(['entityType' => $entityType], []);
        }

        $attributeType = $options->getAttributeInputType();
        if ($attributeType && gettype($attributeId) != $attributeType) {
            $violations[] = $this->createAttributeTypeViolation(['attributeType' => $attributeType], $attributePath);
        }

        $violations = array_merge($violations, $this->checkValues($itemIndex, $inputValue, $options));

        return $violations;
    }

    protected function checkAttributeDuplicates(mixed $attributeId, string $attributeProperty, int $itemIndex): array
    {
        $violations = [];
        if (isset($this->duplicatedAttributes[$attributeId])) {
            if (count($this->duplicatedAttributes[$attributeId]) == 1) {
                $attributePath = [current($this->duplicatedAttributes[$attributeId]), $attributeProperty];
                $violations[] = $this->createDuplicatedAttributeViolation([], $attributePath);
            }

            $attributePath = [$itemIndex, $attributeProperty];
            $violations[] = $this->createDuplicatedAttributeViolation([], $attributePath);
        }

        $this->duplicatedAttributes[$attributeId][] = $itemIndex;

        return $violations;
    }

    protected function checkValues(int $itemIndex, mixed $inputValue, InverterOptionsInterface $options): array
    {
        $reversMapping = $options->getReversePropertyMapping();
        $valueProperty = $options->isMultiple() ? $reversMapping->getValues() : $reversMapping->getValue();
        $valuePath = [$itemIndex, $valueProperty];
        $violations = [];

        $valuePassed = true;
        if (!$options->isMultiple()) {
            if (!$options->isIgnoreInputEmptyValue() && empty($inputValue)) {
                $violations[] = $this->createEmptyValueViolation([], $valuePath);
                $valuePassed = false;
            }

            if (is_array($inputValue)) {
                $violations[] = $this->createScalarItemViolation([], $valuePath);
                $valuePassed = false;
            }
        }

        if ($options->isMultiple() && !is_array($inputValue)) {
            $violations[] = $this->createArrayItemViolation([], $valuePath);
            $valuePassed = false;
        }

        if ($valuePassed) {
            $valueType = $options->getValueInputType();
            if ($options->isMultiple()) {
                $duplicateHashes = [];
                foreach ($inputValue as $key => $value) {
                    $fullValuePath = $valuePath;
                    $fullValuePath[] = $key;
                    $valueId = $value instanceof EavValueInterface ? $value->getId() : $value;
                    $duplicateHashes[$valueId][] = $key;

                    if (!$options->isIgnoreInputEmptyValue() && empty($value)) {
                        $violations[] = $this->createEmptyValueViolation([], $fullValuePath);
                    } else if ($valueType) {
                        if (gettype($value) != $valueType) {
                            $violations[] = $this->createValueTypeViolation(['valueType' => $valueType], $fullValuePath);
                        }
                    }
                }

                $violations = array_merge($violations, $this->checkValueDuplicates($duplicateHashes, $valuePath));
            } else if ($valueType) {
                if (gettype($inputValue) != $valueType) {
                    $violations[] = $this->createValueTypeViolation(['valueType' => $valueType], $valuePath);
                }
            }
        }

        return $violations;
    }

    /**
     * @return array<EavInverterViolation>
     */
    protected function checkValueDuplicates(array $duplicateHashes, array $valuePath): array
    {
        $violations = [];
        foreach ($duplicateHashes as $duplicates) {
            if (count($duplicates) > 1) {
                foreach ($duplicates as $index) {
                    $fullValuePath = array_merge($valuePath, [$index]);
                    $violations[] = $this->createDuplicatedValueViolation([], $fullValuePath);
                }
            }
        }

        return $violations;
    }

    protected function createDuplicatedAttributeViolation(array $parameters, array $path): EavInverterViolation
    {
        return $this->createItemViolation('Duplicated attribute', $parameters, $path);
    }

    protected function createDuplicatedValueViolation(array $parameters, array $path): EavInverterViolation
    {
        return $this->createItemViolation('Duplicated value', $parameters, $path);
    }

    protected function createArrayItemViolation(array $parameters, array $path): EavInverterViolation
    {
        return $this->createItemViolation('Value should be array', $parameters, $path);
    }

    protected function createScalarItemViolation(array $parameters, array $path): EavInverterViolation
    {
        return $this->createItemViolation('Value should be scalar', $parameters, $path);
    }

    protected function createEntityTypeViolation(array $parameters, array $path): EavInverterViolation
    {
        return $this->createItemViolation('Entity type should be "{type}"', $parameters, $path);
    }

    protected function createAttributeTypeViolation(array $parameters, array $path): EavInverterViolation
    {
        return $this->createItemViolation('Attribute type should be "{type}"', $parameters, $path);
    }

    protected function createValueTypeViolation(array $parameters, array $path): EavInverterViolation
    {
        return $this->createItemViolation('Value type should be "{type}"', $parameters, $path);
    }

    protected function createEmptyValueViolation(array $parameters, array $path): EavInverterViolation
    {
        return $this->createItemViolation('Value should not be empty', $parameters, $path);
    }

    protected function createItemViolation(string $message, array $parameters, array $path): EavInverterViolation
    {
        return $this->inverterViolationFactory->create($message, $parameters, $path);
    }
}
