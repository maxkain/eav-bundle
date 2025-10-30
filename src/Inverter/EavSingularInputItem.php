<?php

namespace Maxkain\EavBundle\Inverter;

class EavSingularInputItem
{
    /**
     * @param scalar $value Plain value or plain value id.
     */
    public function __construct(
        public mixed $attribute,
        public mixed $value,
    ) {
    }
}
