<?php

/*
 * This file is part of the MicroSymfony package.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MicroSymfony\Component\DependencyInjection\Loader;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

trait FileLoaderTrait
{
    protected bool $prepend = false;
    protected array $extensionConfigs = [];
    protected int $importing = 0;

    public function __construct(ContainerBuilder $container, FileLocatorInterface $locator, string $env = null, bool $prepend = false)
    {
        $this->prepend = $prepend;

        parent::__construct($container, $locator, $env);
    }

    public function import(mixed $resource, string $type = null, bool|string $ignoreErrors = false, string $sourceResource = null, $exclude = null): mixed
    {
        ++$this->importing;
        try {
            return parent::import($resource, $type, $ignoreErrors, $sourceResource, $exclude);
        } finally {
            --$this->importing;
        }
    }

    protected function loadExtensionConfig(string $namespace, array $config): void
    {
        if (!$this->prepend) {
            $this->container->loadFromExtension($namespace, $config);

            return;
        }

        if ($this->importing) {
            if (!isset($this->extensionConfigs[$namespace])) {
                $this->extensionConfigs[$namespace] = [];
            }
            array_unshift($this->extensionConfigs[$namespace], $config);

            return;
        }

        $this->container->prependExtensionConfig($namespace, $config);
    }

    protected function loadExtensionConfigs(): void
    {
        if ($this->importing || [] === $this->extensionConfigs) {
            return;
        }

        foreach ($this->extensionConfigs as $namespace => $configs) {
            foreach ($configs as $config) {
                $this->container->prependExtensionConfig($namespace, $config);
            }
        }

        $this->extensionConfigs = [];
    }
}
