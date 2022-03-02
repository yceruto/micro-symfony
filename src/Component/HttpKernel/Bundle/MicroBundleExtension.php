<?php

namespace MicroSymfony\Component\HttpKernel\Bundle;

use MicroSymfony\Component\Config\Definition\MicroConfiguration;
use MicroSymfony\Component\DependencyInjection\Extension\MicroContainerExtensionTrait;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

final class MicroBundleExtension extends Extension implements PrependExtensionInterface
{
    use MicroContainerExtensionTrait;

    private MicroBundle $bundle;
    private string $alias;

    public function __construct(MicroBundle $bundle, string $alias)
    {
        $this->bundle = $bundle;
        $this->alias = $alias;
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ?ConfigurationInterface
    {
        return new MicroConfiguration($this->bundle, $container, $this->getAlias());
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getXsdValidationBasePath()
    {
        return false;
    }

    public function prepend(ContainerBuilder $container): void
    {
        $callback = function (ContainerConfigurator $configurator) use ($container) {
            $this->bundle->prependExtension($configurator, $container);
        };

        $this->executeConfiguratorCallback($container, $callback, $this->bundle);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);

        $callback = function (ContainerConfigurator $configurator) use ($config, $container) {
            $this->bundle->loadExtension($config, $configurator, $container);
        };

        $this->executeConfiguratorCallback($container, $callback, $this->bundle);
    }
}
