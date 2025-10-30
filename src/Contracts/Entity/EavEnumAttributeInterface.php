<?php

namespace Maxkain\EavBundle\Contracts\Entity;

interface EavEnumAttributeInterface extends EavAttributeInterface
{
    /**
     * @return iterable<EavValueInterface>
     */
    public function getValues(): iterable;
}
