<?php

namespace Drivers\Messenger\MessageTemplates;

class Button implements \JsonSerializable
{
    public static function create($text, $type = 'button', $value = ''): static
    {
        return new static($text, $type, $value);
    }

    public function __construct(protected string $text, protected string $type = 'button', protected string $value = '') {}

    /**
     * @return array
     */
    public function toArray()
    {
        $payload = [
            "title" => $this->text,
            "type" => $this->type,
        ];

        switch ($this->type) {
            case "postback":
                $payload['payload'] = $this->value;

            case "web_url":
                $payload["url"] = $this->value;
        }

        return $payload;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
