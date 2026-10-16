<?php

namespace Maxkain\EavBundle\Inverter;

use Maxkain\EavBundle\Contracts\Entity\EavInterface;
use Maxkain\EavBundle\Inverter\Options\InverterOptionsInterface;

class InverterProcessor
{
    public function __construct(
        protected RowInverter $rowInverter,
    ) {
    }

    /**
     * @param scalar $entity
     * @param EavSingularInputItem|EavMultipleInputItem $item
     */
    public function processItem(mixed $entity, mixed $item, int $itemIndex, InverterOptionsInterface $options): InverterOutput
    {
        return $item instanceof EavSingularInputItem
            ? $this->invertSingularItem($entity, $item, $itemIndex, $options)
            : $this->invertMultipleItem($entity, $item, $itemIndex, $options);
    }

    protected function invertSingularItem(
        mixed $entity,
        EavSingularInputItem $item,
        int $itemIndex,
        InverterOptionsInterface $options
    ): InverterOutput {
        $output = new InverterOutput();
        $mapping = $options->getPropertyMapping();

        $attribute = $item->attribute;
        $keyCriteria = [
            $mapping->getEntity() => $entity,
            $mapping->getAttribute() => $attribute,
        ];

        $data = $this->rowInverter->invert($entity, $attribute, $item->value, $keyCriteria, $itemIndex, null, $options);
        if ($data instanceof EavInterface) {
            $output->eavs[] = $data;
        } else if ($data instanceof EavInverterViolation) {
            $output->violations[] = $data;
        }

        return $output;
    }

    /**
     * @param scalar $entity
     */
    protected function invertMultipleItem(
        mixed $entity,
        EavMultipleInputItem $item,
        int $itemIndex,
        InverterOptionsInterface $options
    ): InverterOutput {
        $output = new InverterOutput();
        $mapping = $options->getPropertyMapping();

        $attribute = $item->attribute;

        foreach ($item->values as $key => $value) {
            $keyCriteria = [
                $mapping->getEntity() => $entity,
                $mapping->getAttribute() => $attribute,
                $mapping->getValue() => $value,
            ];

            $data = $this->rowInverter->invert($entity, $attribute, $value, $keyCriteria, $itemIndex, $key, $options);
            if ($data instanceof EavInterface) {
                $output->eavs[] = $data;
            } else if ($data instanceof EavInverterViolation) {
                $output->violations[] = $data;
            }
        }

        return $output;
    }
}
