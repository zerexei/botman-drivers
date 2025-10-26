<?php

namespace Drivers\Viber\MessageTemplates;

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
            "attachment" =>  [
                "type" => "template",
                "payload" =>  [
                    "template_type" => "media",
                    "elements" => [
                        [
                            "media_type" => "image",
                            "url" => $this->text
                        ]
                    ]
                ]
            ]
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
