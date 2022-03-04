<?php

namespace MicroSymfony\Component\DependencyInjection\Extension;

use Symfony\Component\Config\Builder\ConfigBuilderGenerator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

trait MicroContainerExtensionTrait
{
    private function executeConfiguratorCallback(ContainerBuilder $container, \Closure $callback, object $subject): void
    {
        $env = $container->getParameter('kernel.environment');
        $loader = $this->createContainerLoader($container, $env);
        $file = (new \ReflectionObject($subject))->getFileName();
        $bundleLoader = $loader->getResolver()->resolve($file);
        if (!$bundleLoader instanceof PhpFileLoader) {
            throw new \LogicException('Unable to create the ContainerConfigurator.');
        }
        $bundleLoader->setCurrentDir(\dirname($file));
        $instanceof = &\Closure::bind(function &() { return $this->instanceof; }, $bundleLoader, $bundleLoader)();

        try {
            $callback(new ContainerConfigurator($container, $bundleLoader, $instanceof, $file, $file, $env));
        } finally {
            $instanceof = [];
            $bundleLoader->registerAliasesForSinglyImplementedInterfaces();
        }
    }

    private function createContainerLoader(ContainerBuilder $container, string $env): DelegatingLoader
    {
        $buildDir = $container->getParameter('kernel.build_dir');
        $locator = new FileLocator();
        $resolver = new LoaderResolver([
            new XmlFileLoader($container, $locator, $env),
            new YamlFileLoader($container, $locator, $env),
            new IniFileLoader($container, $locator, $env),
            new PhpFileLoader($container, $locator, $env, new ConfigBuilderGenerator($buildDir)),
            new GlobFileLoader($container, $locator, $env),
            new DirectoryLoader($container, $locator, $env),
            new ClosureLoader($container, $env),
        ]);

        return new DelegatingLoader($resolver);
    }
}