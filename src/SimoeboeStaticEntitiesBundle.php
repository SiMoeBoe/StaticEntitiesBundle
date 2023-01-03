<?php

namespace Simoeboe\StaticEntitiesBundle;

use Simoeboe\StaticEntitiesBundle\Command\SyncStaticEntitiesCommand;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SimoeboeStaticEntitiesBundle extends AbstractBundle
{

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->registerForAutoconfiguration(StaticEntityCreatorInterface::class)->addTag('simoeboe.static_entity_creator');
        $container->import('../config/services.yaml');

        //var_dump($builder->findTaggedServiceIds('simoeboe.static_entity_creator')); die();
    }
}