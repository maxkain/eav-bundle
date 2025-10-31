<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\Query;

use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;

class AliasGenerator
{
    public function generate(string $name, int $eavIndex, mixed $attribute, mixed $suffix = null): string
    {
        $attributeId = $attribute instanceof EavAttributeInterface ? $attribute->getId() : $attribute;
        $alias = $name . '_' . $eavIndex . '_' . $attributeId;
        if (isset($suffix)) {
            $alias .= '_' . $suffix;
        }

        return $alias;
    }
}
