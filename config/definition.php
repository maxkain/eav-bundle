<?php

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition): void {
    $definition->rootNode()
        ->children()
            ->booleanNode('enable_orphaned_eavs_listener')->defaultTrue()->end()
        ->end()
    ;
};
