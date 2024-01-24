<?php

namespace MicroSymfony\Tests\Component\HttpKernel\Bundle;

use MicroSymfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use MicroSymfony\Component\HttpKernel\Bundle\AbstractBundle;
use MicroSymfony\Tests\Component\DependencyInjection\Extension\AbstractTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class AbstractBundleTest extends AbstractTestCase
{
    public function testConfiguration(): void
    {
        $bundle = new class extends AbstractBundle
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

        self::assertSame($expected, $this->processConfiguration($bundle));
    }


    public function testPrependAppendExtensionConfig(): void
    {
        $bundle = new class extends AbstractBundle
        {
            public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
            {
                // prepend config from plain array
                $builder->prependExtensionConfig('third', ['foo' => 'array']);

                // prepend config from file
                $container->import('../../../fixtures/config/prepend/third.yaml');
            }
        };

        $container = $this->processPrependExtension($bundle->getContainerExtension());

        $expected = [
            ['foo' => 'file_use'],
            ['foo' => 'file'],
            ['foo' => 'array'],
            ['foo' => 'bar'],
        ];

        self::assertSame($expected, $container->getExtensionConfig('third'));
    }

    public function testLoadExtension(): void
    {
        $bundle = new class extends AbstractBundle
        {
            protected string $extensionAlias = 'micro';

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
        };

        $container = $this->processLoadExtension($bundle->getContainerExtension(), [['foo' => 'bar']]);

        self::assertSame(['foo' => 'bar'], $container->getParameter('foo_param'));
        self::assertTrue($container->hasDefinition('foo_service'));
        self::assertTrue($container->hasDefinition('bar_service'));
    }
}
