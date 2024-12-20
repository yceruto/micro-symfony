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

/**
 * A server event to send as part of the SSE streaming technique.
 *
 * @author Yonel Ceruto <open@yceruto.dev>
 */
class ServerEvent
{
    /**
     * @param string|iterable<string> $data    The event data field for the message
     * @param string|null             $type    The event type
     * @param int                     $retry   The event reconnection time in milliseconds
     * @param string|null             $id      The event ID to set the EventSource object's last event ID value
     * @param string|null             $comment The event comment
     */
    public function __construct(
        private string|iterable $data,
        private ?string $type = null,
        private int $retry = 0,
        private ?string $id = null,
        private ?string $comment = null,
    ) {
    }

    public function getData(): iterable|string
    {
        return $this->data;
    }

    /**
     * @return $this
     */
    public function setData(iterable|string $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return $this
     */
    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getRetry(): int
    {
        return $this->retry;
    }

    /**
     * @return $this
     */
    public function setRetry(int $retry): static
    {
        $this->retry = $retry;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return $this
     */
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function __toString(): string
    {
        $event = [];

        if ($this->comment) {
            $event[] = \sprintf(': %s', $this->comment);
        }
        if ($this->id) {
            $event[] = \sprintf('id: %s', $this->id);
        }
        if ($this->retry > 0) {
            $event[] = \sprintf('retry: %s', $this->retry);
        }
        if ($this->type) {
            $event[] = \sprintf('event: %s', $this->type);
        }
        if ($this->data) {
            if (is_iterable($this->data)) {
                foreach ($this->data as $data) {
                    $event[] = \sprintf('data: %s', $data);
                }
            } else {
                $event[] = \sprintf('data: %s', $this->data);
            }
        }

        return implode("\n", $event)."\n\n";
    }
}
