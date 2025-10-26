<?php

namespace Drivers\Viber\MessageTemplates;

class ButtonTemplate implements \JsonSerializable
{
    protected array $buttons = [];

    /**
     * @param $text
     * @return static
     */
    public static function create(string $text): static
    {
        return new static($text);
    }

    public function __construct(protected string $text) {}

    public function addButton(\Drivers\Messenger\MessageTemplates\Button $button): self
    {
        $this->buttons[] = $button->toArray();
        return $this;
    }

    public function addButtons(array $buttons): self
    {
        foreach ($buttons as $button) {
            if ($button instanceof \Drivers\Messenger\MessageTemplates\Button) {
                $this->buttons[] = $button->toArray();
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'message' => [
                'attachment' => [
                    'type' => 'template',
                    'payload' => [
                        'template_type' => 'button',
                        'text' => $this->text,
                        'buttons' => $this->buttons
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
