<?php

/*
 * This file is part of the MicroSymfony package.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MicroSymfony\Component\Config\Definition;

use MicroSymfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

interface ConfigurableInterface
{
    /**
     * Generates the configuration tree builder.
     */
    public function configuration(DefinitionConfigurator $definition): void;
}
