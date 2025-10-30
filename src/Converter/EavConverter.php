<?php

namespace Maxkain\EavBundle\Converter;

use Maxkain\EavBundle\Converter\Options\ConverterOptionsInterface;
use Maxkain\EavBundle\Contracts\Entity\EavInterface;
use Maxkain\EavBundle\Contracts\Entity\EavValueInterface;
use Maxkain\EavBundle\Attribute\AttributeChecker;

class EavConverter implements EavConverterInterface
{
    protected ConverterOptionsInterface $options;

    public function __construct(
        protected AttributeChecker $attributeChecker,
    ) {
    }

    /**
     * @param iterable<EavInterface> $eavs
     * @return array<EavSingularOutputItem|EavMultipleOutputItem|array>
     */
    public function convert(iterable $eavs, ConverterOptionsInterface $options): array
    {
        $this->options = $options;
        $items = [];

        foreach ($eavs as $eav) {
            if ($this->attributeChecker->check($eav->getEntity(), $eav->getAttribute())) {
                $attributeId = $eav->getAttribute()->getId();
                $attributeName = $eav->getAttribute()->getName();
                $items[$attributeId] = $items[$attributeId] ?? $this->createOutputItem($attributeId, $attributeName);
                $items[$attributeId] = $this->convertEav($eav, $items[$attributeId]);
            }
        }

        return $this->prepareOutput($items);
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
    ): EavSingularOutputItem|EavMultipleOutputItem {
        return $this->options->isMultiple()
            ? new EavMultipleOutputItem($attributeId, $attributeName, [], [])
            : new EavSingularOutputItem($attributeId, $attributeName, null, null);
    }

    /**
     * @param array<EavSingularOutputItem|EavMultipleOutputItem> $items
     * @return array<EavSingularOutputItem|EavMultipleOutputItem|array>
     */
    protected function prepareOutput(array $items): array
    {
        $options = $this->options;
        $resultItems = [];

        foreach ($items as $item) {
            if ($options->isConvertItemsToArrays()) {
                $resultItems[] = $this->normalizeItem($item);
            } else {
                $resultItems[] = $item;
            }
        }

        return $resultItems;
    }

    protected function normalizeItem(EavSingularOutputItem|EavMultipleOutputItem $item): array
    {
        $options = $this->options;
        $reverseMapping = $options->getReversePropertyMapping();

        if ($item instanceof EavMultipleOutputItem) {
            $valueKey = $reverseMapping->getValues();
            $valueTitleKey = $reverseMapping->getValueTitles();
            $value = $item->values;
            $valueTitle = $item->valueTitles;
        } else {
            $valueKey = $reverseMapping->getValue();
            $valueTitleKey = $reverseMapping->getValueTitle();
            $value = $item->value;
            $valueTitle = $item->valueTitle;
        }

        return [
            $reverseMapping->getAttribute() => $item->attribute,
            $reverseMapping->getAttributeName() => $item->attributeName,
            $valueKey => $value,
            $valueTitleKey => $valueTitle
        ];
    }
}
