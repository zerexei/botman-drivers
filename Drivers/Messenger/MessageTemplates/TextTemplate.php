<?php

namespace Drivers\Template\MessageTemplates;

class TextTemplate implements \JsonSerializable
{
    public static function create(string $text): static
    {
        return new static($text);
    }

    public function __construct(protected string $text) {}

    public function toArray(): array
    {
        return [
            'text' => $this->text
        ];
    }

    /**
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
