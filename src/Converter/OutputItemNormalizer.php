<?php

namespace Maxkain\EavBundle\Converter;

use Maxkain\EavBundle\Converter\Options\ConverterOptionsInterface;

class OutputItemNormalizer
{
    public function normalize(
        EavSingularOutputItem|EavMultipleOutputItem $item,
        ConverterOptionsInterface $options
    ): array {
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
