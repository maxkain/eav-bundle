<?php

namespace Maxkain\EavBundle\Inverter;

use Maxkain\EavBundle\Inverter\Options\InverterOptionsInterface;

class InputItemDenormalizer
{
    public function denormalize(
        array $rawItem,
        InverterOptionsInterface $options
    ): EavSingularInputItem|EavMultipleInputItem {
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
}
