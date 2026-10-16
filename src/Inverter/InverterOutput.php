<?php

namespace Maxkain\EavBundle\Inverter;

use Maxkain\EavBundle\Contracts\Entity\EavInterface;

class InverterOutput
{
    /**
     * @var iterable<EavInverterViolation>
     */
    public iterable $violations = [];

    /**
     * @var iterable<EavInterface>
     */
    public iterable $eavs = [];

    public function isValid(): bool
    {
        return !$this->violations;
    }
}
