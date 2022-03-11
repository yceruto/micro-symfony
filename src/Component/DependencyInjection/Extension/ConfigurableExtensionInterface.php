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

use MicroSymfony\Component\Config\Definition\ConfigurableInterface;
use MicroSymfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

interface ConfigurableExtensionInterface extends ConfigurableInterface
{
    /**
     * {@inheritdoc}
     */
    public function configuration(DefinitionConfigurator $definition): void;

    /**
     * Allow an extension to prepend the extension configurations.
     */
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void;

    /**
     * Loads a specific configuration.
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void;
}
