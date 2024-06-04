<?php

namespace MicroSymfony\Tests\Bundle\FrameworkBundle\Kernel;

use MicroSymfony\Bundle\FrameworkBundle\Kernel\SingleFileMicroKernelTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Attribute\Route;

class SingleFileMicroKernelTraitTest extends TestCase
{
    public function testApp(): void
    {
        $app = new App('test', true);
        $response = $app->__invoke();

        $this->assertSame('Hello World!', $response->getContent());
    }
}

class App extends Kernel
{
    use SingleFileMicroKernelTrait;

    #[Route('/')]
    public function __invoke(): Response
    {
        return new Response('Hello World!');
    }
}
