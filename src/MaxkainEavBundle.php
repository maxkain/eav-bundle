<?php

namespace Maxkain\EavBundle;

use Maxkain\EavBundle\Bridge\Doctrine\EventListener\OrphanedEavsListener;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class MaxkainEavBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');


        $builder->prependExtensionConfig('twig', [
            'paths' => [
                dirname(__DIR__) . '/templates' => 'Eav',
            ],
        ]);

        if ($config['enable_orphaned_eavs_listener']) {
            $container->services()->get(OrphanedEavsListener::class)
                ->tag('doctrine.event_listener', [
                    'event' => 'onFlush',
                ])
                ->tag('doctrine.event_listener', [
                    'event' => 'postFlush',
                ])
            ;
        }
    }
}
