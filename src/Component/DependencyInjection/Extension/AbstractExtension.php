<?php

/*
 * This file is part of the MicroSymfony package.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MicroSymfony\Component\DependencyInjection\Extension;

use MicroSymfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use MicroSymfony\Component\Config\Definition\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

abstract class AbstractExtension extends Extension implements ConfigurableExtensionInterface, PrependExtensionInterface
{
    use ContainerExtensionTrait;

    public function configure(DefinitionConfigurator $definition): void
    {
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
    }

    final public function getConfiguration(array $config, ContainerBuilder $container): ?ConfigurationInterface
    {
        return new Configuration($this, $container, $this->getAlias());
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
