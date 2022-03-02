<?php

namespace MicroSymfony\Component\Config\Definition;

use MicroSymfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

interface MicroConfigurationAwareInterface
{
    public function configuration(DefinitionConfigurator $definition): void;
}
