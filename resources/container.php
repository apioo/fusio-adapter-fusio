<?php

use Fusio\Adapter\Fusio\Action\FusioActionInvoke;
use Fusio\Adapter\Fusio\Connection\Fusio;
use Fusio\Engine\Adapter\ServiceBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    $services = ServiceBuilder::build($container);
    $services->set(Fusio::class);
    $services->set(FusioActionInvoke::class);
};
