<?php

namespace Maxkain\EavBundle\Converter\Options;

interface ConverterOptionsInterface
{
    public function isMultiple(): bool;
    public function isConvertItemsToArrays(): bool;
    public function getReversePropertyMapping(): ConverterReversePropertyMappingInterface;
}
