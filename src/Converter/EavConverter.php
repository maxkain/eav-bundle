<?php

namespace Maxkain\EavBundle\Converter;

use Maxkain\EavBundle\Converter\Options\ConverterOptionsInterface;
use Maxkain\EavBundle\Contracts\Entity\EavInterface;
use Maxkain\EavBundle\Contracts\Entity\EavValueInterface;
use Maxkain\EavBundle\AttributeChecker\AttributeChecker;

class EavConverter implements EavConverterInterface
{
    public function __construct(
        protected AttributeChecker $attributeChecker,
        protected OutputItemNormalizer $outputItemNormalizer
    ) {
    }

    /**
     * @param iterable<EavInterface> $eavs
     * @return array<EavSingularOutputItem|EavMultipleOutputItem|array>
     */
    public function convert(iterable $eavs, ConverterOptionsInterface $options): array
    {
        $items = [];

        foreach ($eavs as $eav) {
            if ($this->attributeChecker->check($eav->getEntity(), $eav->getAttribute(), $options)) {
                $attributeId = $eav->getAttribute()->getId();
                $attributeName = $eav->getAttribute()->getName();
                $items[$attributeId] = $items[$attributeId]
                    ?? $this->createOutputItem($attributeId, $attributeName, $options);
                $items[$attributeId] = $this->convertEav($eav, $items[$attributeId]);
            }
        }

        return $this->prepareOutput($items, $options);
    }

    protected function convertEav(
        EavInterface $eav,
        EavSingularOutputItem|EavMultipleOutputItem $item,
    ): EavSingularOutputItem|EavMultipleOutputItem {
        $value = null;
        $valueTitle = null;

        if ($eav->getId()) {
            $value = $eav->getValue();
            if ($value instanceof EavValueInterface) {
                $valueTitle = $value->getTitle();
                $value = $value->getId();
            }
        }

        if ($item instanceof EavMultipleOutputItem) {
            $item->values[] = $value;
            $item->valueTitles[] = $valueTitle ?? $value;
        } else {
            $item->value = $value;
            $item->valueTitle = $valueTitle ?? $value;
        }

        return $item;
    }

    protected function createOutputItem(
        mixed $attributeId,
        string $attributeName,
        ConverterOptionsInterface $options
    ): EavSingularOutputItem|EavMultipleOutputItem {
        return $options->isMultiple()
            ? new EavMultipleOutputItem($attributeId, $attributeName, [], [])
            : new EavSingularOutputItem($attributeId, $attributeName, null, null);
    }

    /**
     * @param array<EavSingularOutputItem|EavMultipleOutputItem> $items
     * @return array<EavSingularOutputItem|EavMultipleOutputItem|array>
     */
    protected function prepareOutput(array $items, ConverterOptionsInterface $options): array
    {
        $resultItems = [];

        foreach ($items as $item) {
            if ($options->isConvertItemsToArrays()) {
                $resultItems[] = $this->outputItemNormalizer->normalize($item, $options);
            } else {
                $resultItems[] = $item;
            }
        }

        return $resultItems;
    }
}
