<?php

/*
 * This file is part of the MicroSymfony package.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MicroSymfony\Tests\Component\HttpFoundation;

use MicroSymfony\Component\HttpFoundation\EventStreamResponse;
use MicroSymfony\Component\HttpFoundation\ServerEvent;
use PHPUnit\Framework\TestCase;

class EventStreamResponseTest extends TestCase
{
    public function testInitializationWithDefaultValues()
    {
        $response = new EventStreamResponse();

        $this->assertSame('text/event-stream', $response->headers->get('content-type'));
        $this->assertSame('no-cache, private', $response->headers->get('cache-control'));
        $this->assertSame('keep-alive', $response->headers->get('connection'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(0, $response->getRetry());
    }

    public function testStreamSingleEvent()
    {
        $response = new EventStreamResponse(function () {
            yield new ServerEvent(
                data: 'foo',
                type: 'bar',
                retry: 100,
                id: '1',
                comment: 'bla bla',
            );
        });

        $expected = <<<STR
: bla bla
id: 1
retry: 100
event: bar
data: foo


STR;

        $this->assertSameResponseContent($expected, $response);
    }

    public function testStreamEventsAndData()
    {
        $data = static function (): iterable {
            yield 'first line';
            yield 'second line';
            yield 'third line';
        };

        $response = new EventStreamResponse(function () use ($data) {
            yield new ServerEvent('single line');
            yield new ServerEvent(['first line', 'second line']);
            yield new ServerEvent($data());
        });

        $expected = <<<STR
data: single line

data: first line
data: second line

data: first line
data: second line
data: third line


STR;

        $this->assertSameResponseContent($expected, $response);
    }

    public function testStreamEventsWithRetryFallback()
    {
        $response = new EventStreamResponse(function () {
            yield new ServerEvent('foo');
            yield new ServerEvent('bar');
        }, retry: 1500);

        $expected = <<<STR
retry: 1500
data: foo

retry: 1500
data: bar


STR;

        $this->assertSameResponseContent($expected, $response);
    }

    public function testStreamEventWithSendMethod()
    {
        $response = new EventStreamResponse(function (EventStreamResponse $response) {
            $response->sendEvent(new ServerEvent('foo'));
        });

        $this->assertSameResponseContent("data: foo\n\n", $response);
    }

    private function assertSameResponseContent(string $expected, EventStreamResponse $response): void
    {
        ob_start();
        $response->send();
        $actual = ob_get_clean();

        $this->assertSame($expected, $actual);
    }
}
