<?php

namespace Maxkain\EavBundle\Inverter;

use Maxkain\EavBundle\AttributeChecker\AttributeChecker;
use Maxkain\EavBundle\Inverter\CollectionSetter\CollectionSetter;
use Maxkain\EavBundle\Contracts\Entity\EavInterface;
use Maxkain\EavBundle\Inverter\Options\InverterOptionsInterface;

class EavInverter implements EavInverterInterface
{
    public function __construct(
        protected CollectionSetter $collectionSetter,
        protected InverterValidator $inverterValidator,
        protected InputItemDenormalizer $inputItemDenormalizer,
        protected InverterProcessor $inverterProcessor,
        protected AttributeChecker $attributeChecker,
    ) {
    }

    /**
     * @param array<EavSingularInputItem|EavMultipleInputItem|array> $items
     * @param iterable<EavInterface> $eavs Collection of Eav entities.
     */
    public function invert(
        mixed $entity,
        array $items,
        iterable $eavs,
        InverterOptionsInterface $options,
        bool $addOnly = false
    ): InverterOutput {
        $output = $this->invertToArray($entity, $items, $options);
        if ($addOnly) {
            $orphanedEavsChecker = function(EavInterface $eav) use ($options) : bool {
                return $this->attributeChecker->check($eav->getEntity(), $eav->getAttribute(), $options);
            };

            $output->eavs = $this->collectionSetter->add($output->eavs, $eavs, $orphanedEavsChecker);
            return $output;
        }

        $output->eavs = $this->collectionSetter->set($output->eavs, $eavs);

        return $output;
    }

    /**
     * @param array<EavSingularInputItem|EavMultipleInputItem|array> $items
     */
    protected function invertToArray(
        mixed $entity,
        array $items,
        InverterOptionsInterface $options
    ): InverterOutput {
        $output = new InverterOutput();

        foreach ($items as $itemIndex => $item) {
            if (is_array($item)) {
                $item = $this->inputItemDenormalizer->denormalize($item, $options);
            }

            $duplicatedAttributes = [];
            $valueData = $options->isMultiple() ? $item->values : $item->value;
            $violations = $this->inverterValidator->validateItem($entity, $itemIndex, $item->attribute, $valueData,
                $options, $duplicatedAttributes);

            if (count($violations)) {
                $output->violations = array_merge($output->violations, $violations);
            } else {
                $partialOutput = $this->inverterProcessor->processItem($entity, $item, $itemIndex, $options);
                $output->eavs = array_merge($output->eavs, $partialOutput->eavs);
                $output->violations = array_merge($output->violations, $partialOutput->violations);
            }
        }

        return $output;
    }
}
