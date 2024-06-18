<?php

namespace MicroSymfony\Tests\Bundle\FrameworkBundle\Kernel;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SingleFileMicroKernelTraitTest extends TestCase
{
    public function testApp(): void
    {
        $kernel = new SimpleKernel('test', true);
        $kernel->boot();

        $request = Request::create('/');
        $response = $kernel->handle($request, HttpKernelInterface::MAIN_REQUEST, false);

        $this->assertSame('Hello World!', $response->getContent());
    }
}
