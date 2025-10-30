<?php

namespace Maxkain\EavBundle\Inverter;

use Maxkain\EavBundle\Contracts\Entity\EavValueInterface;
use Maxkain\EavBundle\Inverter\Options\InverterOptionsInterface;

class InverterValidator
{
    public function __construct(
        protected InverterViolationFactory $inverterViolationFactory,
    ) {
    }

    /**
     * @return array<EavInverterViolation>
     */
    public function validateItem(
        mixed $entityId,
        int $itemIndex,
        mixed $attributeId,
        mixed $inputValue,
        InverterOptionsInterface $options
    ): array {
        $violations = [];
        $reversMapping = $options->getReversePropertyMapping();
        $attributeName = $reversMapping->getAttribute();
        $valueName = $options->isMultiple() ? $reversMapping->getValues() : $reversMapping->getValue();

        $attributePath = [$itemIndex, $attributeName];
        $valuePath = [$itemIndex, $valueName];

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

        $entityType = $options->getEntityInputType();
        if ($entityType && gettype($entityId) != $entityType) {
            $violations[] = $this->createEntityTypeViolation(['entityType' => $entityType], []);
        }

        $attributeType = $options->getAttributeInputType();
        if ($attributeType && gettype($attributeId) != $attributeType) {
            $violations[] = $this->createAttributeTypeViolation(['attributeType' => $attributeType], $attributePath);
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

                $violations = array_merge($violations, $this->checkDuplicates($duplicateHashes, $valuePath));
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
    protected function checkDuplicates(array $duplicateHashes, array $valuePath): array
    {
        $violations = [];
        foreach ($duplicateHashes as $duplicates) {
            if (count($duplicates) > 1) {
                foreach ($duplicates as $index) {
                    $fullValuePath = array_merge($valuePath, [$index]);
                    $violations[] = $this->createDuplicateItemViolation([], $fullValuePath);
                }
            }
        }

        return $violations;
    }

    protected function createDuplicateItemViolation(array $parameters, array $path): EavInverterViolation
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
