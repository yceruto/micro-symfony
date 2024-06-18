<?php

namespace MicroSymfony\Bundle\FrameworkBundle\Kernel;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\Kernel;

abstract class MicroKernel extends Kernel
{
    use MicroKernelTrait;

    public static function new(): \Closure
    {
        return static function (array $context) {
            $kernel = new static($context['APP_ENV'], (bool) $context['APP_DEBUG']);

            return \in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true) ? new Application($kernel) : $kernel;
        };
    }
}
