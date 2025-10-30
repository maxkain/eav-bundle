<?php

namespace Maxkain\EavBundle\Inverter\Options;

interface InverterPropertyMappingInterface
{
    public function getEntity(): string;
    public function getAttribute(): string;
    public function getValue(): string;
    public function getEntityId(): string;
    public function getAttributeId(): string;
    public function getValueId(): string;
}
