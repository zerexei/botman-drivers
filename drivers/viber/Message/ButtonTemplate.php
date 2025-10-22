<?php

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

    public function addButton(Viber\Message\Button $button): self
    {
        $this->buttons[] = $button->toArray();
        return $this;
    }

    public function addButtons(array $buttons): self
    {
        foreach ($buttons as $button) {
            if ($button instanceof Viber\Message\Button) {
                $this->buttons[] = $button->toArray();
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'Type' => 'rich_media',
            'Buttons' => $this->buttons
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
