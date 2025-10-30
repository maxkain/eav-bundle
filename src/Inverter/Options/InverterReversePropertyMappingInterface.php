<?php

namespace Maxkain\EavBundle\Inverter\Options;

interface InverterReversePropertyMappingInterface
{
    public function getAttribute(): string;
    public function getValues(): string;
    public function getValue(): string;
}
