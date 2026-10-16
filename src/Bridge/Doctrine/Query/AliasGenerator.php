<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\Query;

use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;

class AliasGenerator
{
    public function generate(string $name, int $eavIndex, mixed $attribute = null, mixed $suffix = null): string
    {
        $attribute = $attribute instanceof EavAttributeInterface ? $attribute->getId() : $attribute;
        $attribute = $attribute instanceof QueryFieldPath ? null : $attribute;
        $alias = $name . '_' . $eavIndex;

        if (isset($attribute)) {
            $alias .= '_' . $attribute;
        }

        if (isset($suffix)) {
            $alias .= '_' . $suffix;
        }

        return $alias;
    }
}
