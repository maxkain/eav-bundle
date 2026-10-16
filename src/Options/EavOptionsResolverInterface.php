<?php

namespace Maxkain\EavBundle\Options;

interface EavOptionsResolverInterface
{
    /**
     * @param EavOptionsInterface|string $options Object or eavFqcn or eavKey
     */
    public function resolve(EavOptionsInterface|string $options): EavOptionsInterface;
}
