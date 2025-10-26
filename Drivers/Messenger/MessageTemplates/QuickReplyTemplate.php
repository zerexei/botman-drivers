<?php

namespace Drivers\Viber\MessageTemplates;

class QuickReplyTemplate implements \JsonSerializable
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

    public function addButton(\Drivers\Messenger\MessageTemplates\QuickReplyButton $button): self
    {
        $this->buttons[] = $button->toArray();
        return $this;
    }

    public function addButtons(array $buttons): self
    {
        foreach ($buttons as $button) {
            if ($button instanceof \Drivers\Messenger\MessageTemplates\QuickReplyButton) {
                $this->buttons[] = $button->toArray();
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            "text" => $this->text,
            "quick_replies" => $this->buttons,
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
