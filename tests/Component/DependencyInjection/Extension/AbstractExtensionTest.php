<?php

namespace MicroSymfony\Tests\Component\DependencyInjection\Extension;

use MicroSymfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use MicroSymfony\Component\DependencyInjection\Extension\AbstractExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class AbstractExtensionTest extends AbstractTestCase
{
    public function testConfiguration(): void
    {
        $extension = new class extends AbstractExtension
        {
            public function configure(DefinitionConfigurator $definition): void
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
        $extension = new class extends AbstractExtension
        {
            public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
            {
                // prepend config from plain array
                $builder->prependExtensionConfig('third', ['foo' => 'array']);

                // prepend config from file
                $container->import('../../../fixtures/config/prepend/third.yaml');
            }
        };

        $container = $this->processPrependExtension($extension);

        $expected = [
            ['foo' => 'file_use'],
            ['foo' => 'file_test_use'],
            ['foo' => 'file'],
            ['foo' => 'file_test'],
            ['foo' => 'array'],
            ['foo' => 'bar'],
        ];

        self::assertSame($expected, $container->getExtensionConfig('third'));
    }

    public function testLoadExtension(): void
    {
        $extension = new class extends AbstractExtension
        {
            public function configure(DefinitionConfigurator $definition): void
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
