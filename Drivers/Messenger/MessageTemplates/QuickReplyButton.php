<?php

namespace Drivers\Messenger\MessageTemplates;

class QuickReplyButton implements \JsonSerializable
{
    public static function create($text, $type = 'button', $value = ''): static
    {
        return new static($text, $type, $value);
    }

    public function __construct(protected string $text, protected string $type = 'text', protected string $value = '') {}

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            "content_type" => $this->type,
            "title" => $this->text,
            "payload" => $this->value,
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
