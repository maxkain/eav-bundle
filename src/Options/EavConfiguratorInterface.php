<?php

namespace Maxkain\EavBundle\Options;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eav.configurator')]
interface EavConfiguratorInterface
{
    /**
     * @return array<int|string, EavOptionsInterface>
     */
    public function configure(): array;
}
