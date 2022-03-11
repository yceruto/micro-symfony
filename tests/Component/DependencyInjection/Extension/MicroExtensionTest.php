<?php

namespace MicroSymfony\Tests\Component\DependencyInjection\Extension;

use MicroSymfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use MicroSymfony\Component\DependencyInjection\Extension\MicroExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class MicroExtensionTest extends MicroTestCase
{
    public function testConfiguration(): void
    {
        $extension = new class extends MicroExtension
        {
            public function configuration(DefinitionConfigurator $definition): void
            {
                // load one
                $definition->import('../../../fixtures/config/definition/foo.php');

                // load multiples
                $definition->import('../../../fixtures/config/definition/multiple/*.php');

                // inline
                $definition->rootNode()
                    ->children()
                        ->scalarNode('ping')->defaultValue('inline')->end()
                    ->end();
            }
        };

        $expected = [
            'foo' => 'one',
            'bar' => 'multi',
            'baz' => 'multi',
            'ping' => 'inline',
        ];

        self::assertSame($expected, $this->processConfiguration($extension));
    }

    public function testPrependAppendExtensionConfig(): void
    {
        $extension = new class extends MicroExtension
        {
            public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
            {
                // append config
                $container->extension('third', ['foo' => 'append']);

                // prepend config
                $builder->prependExtensionConfig('third', ['foo' => 'prepend']);
            }
        };

        $container = $this->processPrependExtension($extension);

        $expected = [
            ['foo' => 'prepend'],
            ['foo' => 'bar'],
            ['foo' => 'append'],
        ];

        self::assertSame($expected, $container->getExtensionConfig('third'));
    }

    public function testLoadExtension(): void
    {
        $extension = new class extends MicroExtension
        {
            public function configuration(DefinitionConfigurator $definition): void
            {
                $definition->import('../../../fixtures/config/definition/foo.php');
            }

            public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
            {
                $container->parameters()
                    ->set('foo_param', $config)
                ;

                $container->services()
                    ->set('foo_service', \stdClass::class)
                ;

                $container->import('../../../fixtures/config/services.php');
            }

            public function getAlias(): string
            {
                return 'micro';
            }
        };

        $container = $this->processLoadExtension($extension, [['foo' => 'bar']]);

        self::assertSame(['foo' => 'bar'], $container->getParameter('foo_param'));
        self::assertTrue($container->hasDefinition('foo_service'));
        self::assertTrue($container->hasDefinition('bar_service'));
    }
}
