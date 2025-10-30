<?php

namespace Maxkain\EavBundle\Options;

use Maxkain\EavBundle\Converter\Options\ConverterOptionsInterface;
use Maxkain\EavBundle\Inverter\Options\InverterOptionsInterface;

interface EavOptionsInterface extends ConverterOptionsInterface, InverterOptionsInterface, TagOptionsInterface
{
    public function getIndex(): int;
    public function getPropertyMapping(): PropertyMappingInterface;
    public function getReversePropertyMapping(): ReversePropertyMappingInterface;
}
