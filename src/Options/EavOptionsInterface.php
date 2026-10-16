<?php

namespace Maxkain\EavBundle\Options;

use Maxkain\EavBundle\AttributeFinder\Options\AttributeFinderOptionsInterface;
use Maxkain\EavBundle\Converter\Options\ConverterOptionsInterface;
use Maxkain\EavBundle\Inverter\Options\InverterOptionsInterface;
use Maxkain\EavBundle\Tag\Options\EavOptionsWithTagsInterface;

interface EavOptionsInterface extends ConverterOptionsInterface, InverterOptionsInterface,
    AttributeFinderOptionsInterface, EavOptionsWithTagsInterface
{
    public function getIndex(): int;
    public function getPropertyMapping(): PropertyMappingInterface;
    public function getReversePropertyMapping(): ReversePropertyMappingInterface;
}
