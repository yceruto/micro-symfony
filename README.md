# Micro-Symfony Tools

Class helpers for Symfony applications.

![ci](https://github.com/yceruto/micro-symfony/actions/workflows/ci.yml/badge.svg)

## Installation

```
composer require yceruto/micro-symfony
```

## Micro-Bundle

Bundles are a very important piece of code in your Symfony applications, and most of the time they require special 
configuration and DI extensions to achieve their goal.

In that sense, this `AbstractBundle` class will help you to create a concise and small bundle, fastly, focusing on 
what you only need to define and import by providing useful shortcuts and configurators:

```php
namespace Acme\FooBundle;

use MicroSymfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use MicroSymfony\Component\HttpKernel\Bundle\AbstractBundle;
// ...

class AcmeFooBundle extends AbstractBundle
{
    protected string $extensionAlias = ''; // set here the custom extension alias, e.g. 'foo' (default 'acme_foo')

    public function configuration(DefinitionConfigurator $definition): void
    {
        // loads config definition from a file
        $definition->import('../config/definition.php');

        // loads config definition from multiple files (when it's too long you can split it)
        $definition->import('../config/definition/*.php');

        // defines config directly when it's short
        $definition->rootNode()
            ->children()
                ->scalarNode('foo')->defaultValue('bar')->end()
            ->end()
        ;
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // prepend config to other bundles
        $builder->prependExtensionConfig('framework', [
            'cache' => ['prefix_seed' => 'foo/bar'],
        ]);

        // append config to other bundles
        $container->extension('framework', [
            'cache' => ['prefix_seed' => 'foo/bar'],
        ])

        // append config to other bundles from a config file
        $container->import('../config/packages/cache.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->parameters()
            ->set('foo', $config['foo']);

        $container->import('../config/services.php');

        if ('bar' === $config['foo']) {
            $container->services()
                ->set(Foobar::class);
        }
    }
}
```

With this class you don't have to create a separate class for `Extension` or `Configuration`. Further, all methods contain 
configurators that allow you to import a definition or config file in any supported format (`yaml`, `xml`, `php`, etc.) 

This is how the configuration definition should look when you are importing it from a file:
```php
// acme/foo_bundle/config/definition.php

use MicroSymfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->scalarNode('foo')->defaultValue('bar')->end()
        ->end()
    ;
};
```

## Micro-Extension

In some cases, mainly for bundle-less approach, you might want to add a DI extension to your application without a bundle 
class. This `AbstractExtension` class will help you to simplify your extension definition by providing the same useful 
shortcuts and configurators:

```php
namespace App\FooModule\Infrastructure\Symfony\DependecyInjection;

use MicroSymfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use MicroSymfony\Component\DependencyInjection\Extension\AbstractExtension;
// ...

class FooExtension extends AbstractExtension
{
    public function configuration(DefinitionConfigurator $definition): void
    {
        // loads config definition from a file
        $definition->import('../../config/definition.php');

        // loads config definition from multiple files (when it's too long you can split it)
        $definition->import('../../config/definition/*.php');

        // defines config directly when it's short
        $definition->rootNode()
            ->children()
                ->scalarNode('foo')->defaultValue('bar')->end()
            ->end()
        ;
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // prepend config to other bundles
        $builder->prependExtensionConfig('framework', [
            'cache' => ['prefix_seed' => 'foo/bar'],
        ]);

        // append config to other bundles
        $container->extension('framework', [
            'cache' => ['prefix_seed' => 'foo/bar'],
        ])

        // append config to other bundles from a config file
        $container->import('../../config/packages/cache.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->parameters()
            ->set('foo', $config['foo']);

        $container->import('../../config/services.php');

        if ('bar' === $config['foo']) {
            $container->services()
                ->set(Foobar::class);
        }
    }
}
```

You can register your extension directly into the Kernel this way:
```php
class Kernel extends BaseKernel
{
    protected function build(ContainerBuilder $container): void
    {
        $container->registerExtension(new FooExtension());
    }
}
```

## Symfony 6 Support

This feature is fully implemented since Symfony 6.1, so you can remove 
this package from your dependencies after upgrading accordingly.

### Upgrade Notes

All classes included in this package are registered under the `MicroSymfony` namespace,
however, they follow the same organization that Symfony. Thus, to upgrade just remove 
the `Micro` prefix from all imported classes and everything should keep working as before.

```git
-use MicroSymfony\Component\DependencyInjection\Extension\AbstractExtension;
+use Symfony\Component\DependencyInjection\Extension\AbstractExtension;
```

## License

This software is published under the [MIT License](LICENSE)
