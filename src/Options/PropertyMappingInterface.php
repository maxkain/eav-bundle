<?php

namespace Maxkain\EavBundle\Options;

use Maxkain\EavBundle\Inverter\Options\InverterPropertyMappingInterface;

interface PropertyMappingInterface extends InverterPropertyMappingInterface, TagPropertyMappingInterface
{
    public function getValueAttribute(): string;
    public function getValueTitle(): string;
}
