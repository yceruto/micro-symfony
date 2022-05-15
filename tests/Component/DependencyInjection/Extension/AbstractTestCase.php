<?php

namespace MicroSymfony\Tests\Component\DependencyInjection\Extension;

use MicroSymfony\Component\Config\Definition\ConfigurableInterface;
use MicroSymfony\Component\Config\Definition\Configuration;
use MicroSymfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use MicroSymfony\Component\DependencyInjection\Extension\AbstractExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

abstract class AbstractTestCase extends TestCase
{
    protected function processConfiguration(ConfigurableInterface $configurable): array
    {
        $configuration = new Configuration($configurable, null, 'micro');

        return (new Processor())->process($configuration->getConfigTreeBuilder()->buildTree(), []);
    }

    protected function processPrependExtension(PrependExtensionInterface $extension): ContainerBuilder
    {
        $thirdExtension = new class extends AbstractExtension {
            public function configuration(DefinitionConfigurator $definition): void
            {
                $definition->import('../../../fixtures/config/definition/foo.php');
            }

            public function getAlias(): string
            {
                return 'third';
            }
        };

        $container = $this->createContainerBuilder();
        $container->registerExtension($thirdExtension);
        $container->loadFromExtension('third', ['foo' => 'bar']);

        $extension->prepend($container);

        return $container;
    }

    protected function processLoadExtension(ExtensionInterface $extension, array $configs): ContainerBuilder
    {
        $container = $this->createContainerBuilder();

        $extension->load($configs, $container);

        return $container;
    }

    protected function createContainerBuilder(): ContainerBuilder
    {
        return new ContainerBuilder(new ParameterBag([
            'kernel.environment' => 'test',
            'kernel.build_dir' => 'test',
        ]));
    }
}
