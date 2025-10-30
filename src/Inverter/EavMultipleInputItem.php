<?php

namespace Maxkain\EavBundle\Inverter;

class EavMultipleInputItem
{
    /**
     * @param array<scalar> $values Plain values or plain value ids.
     */
    public function __construct(
        public mixed $attribute,
        public array $values
    ) {
    }
}
