<?php

namespace Drivers\Viber\MessageTemplates;

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
        $payload =  [
            'ActionType' => 'reply',
            'ActionBody' => $this->text,
            'Text' => $this->text,
            'Rows' => 1,
        ];

        if ($this->type === 'open-url') {
            $payload['ActionType'] = 'open-url';
            $payload['ActionBody'] = $this->value;
            $payload['Silent'] = true;
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
