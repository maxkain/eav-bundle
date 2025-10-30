<?php

namespace Maxkain\EavBundle\Converter;

use Maxkain\EavBundle\Contracts\Entity\EavInterface;
use Maxkain\EavBundle\Converter\Options\ConverterOptionsInterface;

interface EavConverterInterface
{
    /**
     * @param iterable<EavInterface> $eavs
     * @return array<EavSingularOutputItem|EavMultipleOutputItem|array>
     */
    public function convert(iterable $eavs, ConverterOptionsInterface $options): array;
}
