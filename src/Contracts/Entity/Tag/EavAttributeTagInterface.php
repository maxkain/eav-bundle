<?php

namespace Maxkain\EavBundle\Contracts\Entity\Tag;

use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;

interface EavAttributeTagInterface
{
    public function getTag(): EavTagInterface;
    public function getAttribute(): EavAttributeInterface;
}
