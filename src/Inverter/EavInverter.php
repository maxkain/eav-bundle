<?php

namespace Maxkain\EavBundle\Inverter;

use Maxkain\EavBundle\Attribute\AttributeFinder;
use Maxkain\EavBundle\Options\EavOptionsRegistry;
use Maxkain\EavBundle\Utils\CollectionSetter\CollectionSetter;
use Maxkain\EavBundle\Contracts\Entity\EavInterface;
use Maxkain\EavBundle\Inverter\Options\InverterOptionsInterface;

class EavInverter implements EavInverterInterface
{
    use EavInverterTrait;

    public function __construct(
        protected CollectionSetter $collectionSetter,
        protected InverterValidator $inverterValidator,
        protected AttributeFinder $attributeFinder,
        protected EavOptionsRegistry $optionsRegistry,
        protected RowInverter $rowInverter
    ) {
    }

    /**
     * @param array<EavSingularInputItem|EavMultipleInputItem|array> $items
     * @param iterable<EavInterface> $eavs Collection of Eav entities.
     * @return iterable<EavInterface> Collection of Eav entities.
     */
    public function invert(
        mixed $entity,
        array $items,
        iterable $eavs,
        InverterOptionsInterface|string $options,
        bool $withAddOnly = false
    ): iterable {
        if ($withAddOnly) {
            return $this->add($entity, $items, $eavs, $options);
        }

        return $this->set($entity, $items, $eavs, $options);
    }

    protected function denormalizeInputItem(array $rawItem): EavSingularInputItem|EavMultipleInputItem
    {
        $options = $this->options;
        $reverseMapping = $options->getReversePropertyMapping();

        if ($options->isMultiple()) {
            return new EavMultipleInputItem(
                $rawItem[$reverseMapping->getAttribute()],
                $rawItem[$reverseMapping->getValues()]
            );
        }

        return new EavSingularInputItem(
            $rawItem[$reverseMapping->getAttribute()],
            $rawItem[$reverseMapping->getValue()]
        );
    }

    /**
     * @param scalar $entity
     * @param EavSingularInputItem|EavMultipleInputItem $item
     * @return array<EavInterface>
     */
    protected function processItem(mixed $entity, mixed $item, int $itemIndex): array
    {
        return $item instanceof EavSingularInputItem
            ? $this->invertSingularItem($entity, $item, $itemIndex)
            : $this->invertMultipleItem($entity, $item, $itemIndex);
    }

    /**
     * @return array<EavInterface>
     */
    protected function invertSingularItem(mixed $entity, EavSingularInputItem $item, int $itemIndex): array
    {
        $options = $this->options;
        $mapping = $options->getPropertyMapping();

        $attribute = $item->attribute;
        $keyCriteria = [
            $mapping->getEntity() => $entity,
            $mapping->getAttribute() => $attribute,
        ];

        $data = $this->rowInverter->invert($entity, $attribute, $item->value, $keyCriteria, $itemIndex, null, $options);
        if ($data instanceof EavInterface) {
            return [$data];
        } else if ($data instanceof EavInverterViolation) {
            $this->violations[] = $data;
        }

        return [];
    }

    /**
     * @param scalar $entity
     * @return array<EavInterface>
     */
    protected function invertMultipleItem(mixed $entity, EavMultipleInputItem $item, int $itemIndex): array
    {
        $options = $this->options;
        $mapping = $options->getPropertyMapping();

        $attribute = $item->attribute;
        $rows = [];

        foreach ($item->values as $key => $value) {
            $keyCriteria = [
                $mapping->getEntity() => $entity,
                $mapping->getAttribute() => $attribute,
                $mapping->getValue() => $value,
            ];

            $data = $this->rowInverter->invert($entity, $attribute, $value, $keyCriteria, $itemIndex, $key, $options);
            if ($data instanceof EavInterface) {
                $rows[] = $data;
            } else if ($data instanceof EavInverterViolation) {
                $this->violations[] = $data;
            }
        }

        return $rows;
    }

    /**
     * @param EavSingularInputItem|EavMultipleInputItem $item
     * @return scalar
     */
    protected function getItemAttribute(mixed $item): mixed
    {
        return $item->attribute;
    }

    /**
     * @param EavSingularInputItem|EavMultipleInputItem $item
     * @return scalar|array<scalar>
     */
    protected function getItemValueData(mixed $item): mixed
    {
        return $this->options->isMultiple() ? $item->values : $item->value;
    }
}
