<?php

/*
 * This file is part of the MicroSymfony package.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MicroSymfony\Component\Config\Definition\Loader;

use MicroSymfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DefinitionFileLoader extends FileLoader
{
    private TreeBuilder $treeBuilder;
    private ?ContainerBuilder $container;

    public function __construct(TreeBuilder $treeBuilder, FileLocatorInterface $locator, ContainerBuilder $container = null)
    {
        $this->treeBuilder = $treeBuilder;
        $this->container = $container;

        parent::__construct($locator);
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function load($resource, string $type = null)
    {
        // the loader variable is exposed to the included file below
        $loader = $this;

        $path = $this->locator->locate($resource);
        $this->setCurrentDir(\dirname($path));
        if (null !== $this->container) {
            $this->container->fileExists($path);
        }

        // the closure forbids access to the private scope in the included file
        $load = \Closure::bind(static function ($file) use ($loader) {
            return include $file;
        }, null, ProtectedDefinitionFileLoader::class);

        $callback = $load($path);

        if (\is_object($callback) && \is_callable($callback)) {
            $this->executeCallback($callback, new DefinitionConfigurator($this->treeBuilder, $this, $path, $resource), $path);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, string $type = null): bool
    {
        if (!\is_string($resource)) {
            return false;
        }

        if (null === $type && 'php' === pathinfo($resource, \PATHINFO_EXTENSION)) {
            return true;
        }

        return 'php' === $type;
    }

    private function executeCallback(callable $callback, DefinitionConfigurator $configurator, string $path): void
    {
        if (!$callback instanceof \Closure) {
            $callback = \Closure::fromCallable($callback);
        }

        $arguments = [];
        $r = new \ReflectionFunction($callback);

        foreach ($r->getParameters() as $parameter) {
            $reflectionType = $parameter->getType();

            if (!$reflectionType instanceof \ReflectionNamedType) {
                throw new \InvalidArgumentException(sprintf('Could not resolve argument "$%s" for "%s". You must typehint it (for example with "%s").', $parameter->getName(), $path, DefinitionConfigurator::class));
            }

            switch ($reflectionType->getName()) {
                case DefinitionConfigurator::class:
                    $arguments[] = $configurator;
                    break;
                case TreeBuilder::class:
                    $arguments[] = $this->treeBuilder;
                    break;
                case FileLoader::class:
                case self::class:
                    $arguments[] = $this;
            }
        }

        $callback(...$arguments);
    }
}

/**
 * @internal
 */
final class ProtectedDefinitionFileLoader extends DefinitionFileLoader
{
}
