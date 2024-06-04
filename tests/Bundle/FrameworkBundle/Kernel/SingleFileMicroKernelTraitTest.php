<?php

namespace MicroSymfony\Tests\Bundle\FrameworkBundle\Kernel;

use MicroSymfony\Bundle\FrameworkBundle\Kernel\SingleFileKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SingleFileMicroKernelTraitTest extends TestCase
{
    public function testApp(): void
    {
        $kernel = new Kernel('test', true);
        $response = $kernel->__invoke();

        $this->assertSame('Hello World!', $response->getContent());
    }
}

class Kernel extends SingleFileKernel
{
    #[Route('/')]
    public function __invoke(): Response
    {
        return new Response('Hello World!');
    }
}
