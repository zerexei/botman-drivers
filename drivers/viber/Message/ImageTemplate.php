<?php

class ImageTemplate implements \JsonSerializable
{
    public static function create(string $text): static
    {
        return new static($text);
    }

    public function __construct(protected string $text) {}

    public function toArray(): array
    {
        return [
            "type" => "picture",
            "media" => $this->text,
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
