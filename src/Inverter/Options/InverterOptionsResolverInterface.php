<?php

namespace Maxkain\EavBundle\Inverter\Options;

use Maxkain\EavBundle\Inverter\Options\InverterOptionsInterface;

interface InverterOptionsResolverInterface
{
    public function resolve(InverterOptionsInterface|string $options): ?InverterOptionsInterface;
}
