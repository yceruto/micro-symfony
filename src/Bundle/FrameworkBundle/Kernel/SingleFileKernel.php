<?php

namespace MicroSymfony\Bundle\FrameworkBundle\Kernel;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\Kernel;

abstract class SingleFileKernel extends Kernel
{
    use SingleFileMicroKernelTrait;

    public static function new(string $env, bool $debug): static|Application
    {
        $kernel = new static($env, $debug);

        if (\in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
            return new Application($kernel);
        }

        return $kernel;
    }
}
