<?php

namespace Maxkain\EavBundle\Converter;

class EavSingularOutputItem
{
    /**
     * @param scalar $value Plain value or plain value id.
     * @param scalar $valueTitle Plain value.
     */
    public function __construct(
        public mixed $attribute,
        public string $attributeName,
        public mixed $value,
        public mixed $valueTitle
    ) {
    }
}
