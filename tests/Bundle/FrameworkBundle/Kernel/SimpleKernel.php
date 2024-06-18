<?php

namespace MicroSymfony\Tests\Bundle\FrameworkBundle\Kernel;

use MicroSymfony\Bundle\FrameworkBundle\Kernel\MicroKernel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SimpleKernel extends MicroKernel
{
    #[Route('/')]
    public function __invoke(UrlGeneratorInterface $urlGenerator): Response
    {
        return new Response('Hello World!');
    }
}
