<?php

namespace Maxkain\EavBundle\Options;

use Maxkain\EavBundle\Converter\Options\ConverterReversePropertyMappingInterface;
use Maxkain\EavBundle\Inverter\Options\InverterReversePropertyMappingInterface;

interface ReversePropertyMappingInterface extends ConverterReversePropertyMappingInterface,
    InverterReversePropertyMappingInterface
{

}
