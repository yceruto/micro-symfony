<?php

/*
 * This file is part of the MicroSymfony package.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MicroSymfony\Component\HttpKernel\Bundle;

use MicroSymfony\Component\Config\Definition\Configuration;
use MicroSymfony\Component\DependencyInjection\Extension\ConfigurableExtensionInterface;
use MicroSymfony\Component\DependencyInjection\Extension\ContainerExtensionTrait;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

final class BundleExtension extends Extension implements PrependExtensionInterface
{
    use ContainerExtensionTrait;

    private ConfigurableExtensionInterface $subject;
    private string $alias;

    public function __construct(ConfigurableExtensionInterface $subject, string $alias)
    {
        $this->subject = $subject;
        $this->alias = $alias;
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ?ConfigurationInterface
    {
        return new Configuration($this->subject, $container, $this->getAlias());
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function prepend(ContainerBuilder $container): void
    {
        $callback = function (ContainerConfigurator $configurator) use ($container) {
            $this->subject->prependExtension($configurator, $container);
        };

        $this->executeConfiguratorCallback($container, $callback, $this->subject);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);

        $callback = function (ContainerConfigurator $configurator) use ($config, $container) {
            $this->subject->loadExtension($config, $configurator, $container);
        };

        $this->executeConfiguratorCallback($container, $callback, $this->subject);
    }
}
