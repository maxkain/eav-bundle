<?php

namespace Maxkain\EavBundle\Inverter;

use Maxkain\EavBundle\Contracts\Entity\EavInterface;
use Maxkain\EavBundle\Inverter\Options\InverterOptionsInterface;

interface EavInverterInterface
{
    /**
     * @param iterable<EavInterface> $eavs Collection of Eav entities.
     */
    public function invert(
        mixed $entity,
        array $items,
        iterable $eavs,
        InverterOptionsInterface $options,
        bool $addOnly = false
    ): InverterOutput;
}
