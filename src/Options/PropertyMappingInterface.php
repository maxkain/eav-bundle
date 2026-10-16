<?php

namespace Maxkain\EavBundle\Options;

use Maxkain\EavBundle\AttributeFinder\Options\AttributeFinderPropertyMappingInterface;
use Maxkain\EavBundle\Inverter\Options\InverterPropertyMappingInterface;

interface PropertyMappingInterface extends InverterPropertyMappingInterface, AttributeFinderPropertyMappingInterface
{
    public function getValueAttribute(): string;
    public function getValueTitle(): string;
}
