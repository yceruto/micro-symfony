<?php

namespace MicroSymfony\Component\DependencyInjection\Extension;

use MicroSymfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use MicroSymfony\Component\Config\Definition\MicroConfiguration;
use MicroSymfony\Component\Config\Definition\MicroConfigurationAwareInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

abstract class MicroExtension extends Extension implements MicroConfigurationAwareInterface, PrependExtensionInterface
{
    use MicroContainerExtensionTrait;

    public function configuration(DefinitionConfigurator $definition): void
    {
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ?ConfigurationInterface
    {
        return new MicroConfiguration($this, $container, $this->getAlias());
    }

    public function getXsdValidationBasePath(): bool
    {
        return false;
    }

    final public function prepend(ContainerBuilder $container): void
    {
        $callback = function (ContainerConfigurator $configurator) use ($container) {
            $this->prependExtension($configurator, $container);
        };

        $this->executeConfiguratorCallback($container, $callback, $this);
    }

    final public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);

        $callback = function (ContainerConfigurator $configurator) use ($config, $container) {
            $this->loadExtension($config, $configurator, $container);
        };

        $this->executeConfiguratorCallback($container, $callback, $this);
    }
}
