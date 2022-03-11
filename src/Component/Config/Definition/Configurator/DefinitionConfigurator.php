<?php

/*
 * This file is part of the MicroSymfony package.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MicroSymfony\Component\Config\Definition\Configurator;

use MicroSymfony\Component\Config\Definition\Loader\DefinitionFileLoader;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class DefinitionConfigurator
{
    private TreeBuilder $treeBuilder;
    private DefinitionFileLoader $loader;
    private string $path;
    private string $file;

    public function __construct(TreeBuilder $treeBuilder, DefinitionFileLoader $loader, string $path, string $file)
    {
        $this->treeBuilder = $treeBuilder;
        $this->loader = $loader;
        $this->path = $path;
        $this->file = $file;
    }

    final public function import(string $resource, string $type = null, bool $ignoreErrors = false): void
    {
        $this->loader->setCurrentDir(\dirname($this->path));
        $this->loader->import($resource, $type, $ignoreErrors, $this->file);
    }

    final public function rootNode()
    {
        return $this->treeBuilder->getRootNode();
    }

    final public function setPathSeparator(string $separator): void
    {
        $this->treeBuilder->setPathSeparator($separator);
    }
}
