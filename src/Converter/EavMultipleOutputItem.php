<?php

namespace Maxkain\EavBundle\Converter;

class EavMultipleOutputItem
{
    /**
     * @param array<scalar> $values Plain values or plain value ids.
     * @param array<scalar> $valueTitles Plain values.
     */
    public function __construct(
        public mixed $attribute,
        public string $attributeName,
        public array $values,
        public array $valueTitles
    ) {
    }
}
