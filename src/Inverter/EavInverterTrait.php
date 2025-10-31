<?php

namespace Maxkain\EavBundle\Inverter;

use Maxkain\EavBundle\Attribute\AttributeFinder;
use Maxkain\EavBundle\Options\EavOptionsRegistry;
use Maxkain\EavBundle\Utils\CollectionSetter\CollectionSetter;
use Maxkain\EavBundle\Contracts\Entity\EavInterface;
use Maxkain\EavBundle\Inverter\Options\InverterOptionsInterface;

trait EavInverterTrait
{
    protected InverterOptionsInterface $options;

    /**
     * @var array<EavInverterViolation>
     */
    protected array $violations;

    public function __construct(
        protected CollectionSetter $collectionSetter,
        protected InverterValidator $inverterValidator,
        protected AttributeFinder $attributeFinder,
        protected EavOptionsRegistry $optionsRegistry
    ) {
    }

    /**
     * @param iterable<EavInterface> $eavs Collection of Eav entities.
     */
    protected function set(
        mixed $entity,
        array $items,
        iterable $eavs,
        InverterOptionsInterface|string $options
    ): iterable {
        $eavRows = $this->invertToArray($entity, $items, $options);
        $this->collectionSetter->set($eavRows, $eavs);

        return $eavs;
    }

    /**
     * @param iterable<EavInterface> $eavs Collection of Eav entities.
     */
    protected function add(
        mixed $entity,
        array $items,
        iterable $eavs,
        InverterOptionsInterface|string $options
    ): iterable {
        $eavRows = $this->invertToArray($entity, $items, $options);
        $this->collectionSetter->add($eavRows, $eavs);

        return $eavs;
    }

    /**
     * @return array<EavInterface>
     */
    protected function invertToArray(mixed $entity, array $items, InverterOptionsInterface|string $options): array
    {
        $options = $this->optionsRegistry->resolve($options);
        $this->violations = [];
        $this->options = $options;
        $rows = [];
        $this->inverterValidator->reset();

        foreach ($items as $index => $item) {
            if (is_array($item)) {
                $item = $this->denormalizeInputItem($item);
            }

            $violations = $this->validateItem($entity, $item, $index);

            if (count($violations)) {
                $this->violations = array_merge($this->violations, $violations);
            } else {
                $rows = array_merge($rows, $this->processItem($entity, $item, $index));
            }
        }

        return $rows;
    }

    /**
     * @return array<EavInverterViolation>
     */
    protected function validateItem(mixed $entity, mixed $item, int $itemIndex): array
    {
        return $this->inverterValidator->validateItem($entity, $itemIndex,
            $this->getItemAttribute($item), $this->getItemValueData($item), $this->options);
    }

    public function findAllowedAttributes(mixed $entity, InverterOptionsInterface|string $options): array
    {
        $options = $this->optionsRegistry->resolve($options);

        return $this->attributeFinder->findAllowed($entity, $options);
    }

    /**
     * @return array<EavInverterViolation>
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    public function isValid(): bool
    {
        return (bool) $this->violations;
    }

    abstract protected function getItemAttribute(mixed $item): mixed;
    abstract protected function getItemValueData(mixed $item): mixed;
    abstract protected function denormalizeInputItem(array $rawItem): mixed;
    abstract protected function processItem(mixed $entity, mixed $item, int $itemIndex): array;
}
