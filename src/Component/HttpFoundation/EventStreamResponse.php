<?php

/*
 * This file is part of the MicroSymfony package.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MicroSymfony\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Represents a streaming HTTP response for sending server events
 * as part of the Server-Sent Events (SSE) streaming technique.
 *
 * @see ServerEvent
 *
 * @author Yonel Ceruto <open@yceruto.dev>
 *
 * Example usage:
 *
 *     return new EventStreamResponse(function () {
 *         while (true) {
 *             yield new ServerEvent(time(), type: 'ping');
 *
 *             if (connection_aborted()) {
 *                 break;
 *             }
 *
 *             sleep(1);
 *         }
 *     });
 */
class EventStreamResponse extends StreamedResponse
{
    /**
     * @param int $retry The event reconnection time in milliseconds
     */
    public function __construct(?callable $callback = null, int $status = 200, array $headers = [], private int $retry = 0)
    {
        $headers += [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ];

        parent::__construct($callback, $status, $headers);
    }

    public function setCallback(callable $callback): static
    {
        if ($this->callback) {
            return parent::setCallback($callback);
        }

        $this->callback = function () use ($callback) {
            if (is_iterable($events = $callback($this))) {
                foreach ($events as $event) {
                    $this->sendEvent($event);
                }
            }
        };

        return $this;
    }

    /**
     * @param bool $flush Whether output buffers should be flushed
     *
     * @return $this
     */
    public function sendEvent(ServerEvent $event, bool $flush = true): static
    {
        if ($this->retry > 0 && 0 === $event->getRetry()) {
            $event->setRetry($this->retry);
        }

        echo $event;

        if ($flush && !\in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
            static::closeOutputBuffers(0, true);
            flush();
        }

        return $this;
    }

    public function getRetry(): int
    {
        return $this->retry;
    }

    public function setRetry(int $retry): void
    {
        $this->retry = $retry;
    }
}
