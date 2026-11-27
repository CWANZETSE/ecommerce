<?php

namespace App\Services\AfricasTalking;

class AfricasTalkingMessage
{
    public function __construct(
        public string $content,
        public ?string $from = null,
        public bool $enqueue = true,
    ) {}

    public static function create(string $content): self
    {
        return new self($content);
    }

    public function from(string $senderId): self
    {
        $this->from = $senderId;

        return $this;
    }

    public function enqueue(bool $enqueue = true): self
    {
        $this->enqueue = $enqueue;

        return $this;
    }
}
