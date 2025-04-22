<?php

class GalleryTemplate implements \JsonSerializable
{
    protected array $buttons = [];

    public static function create(string $text): static
    {
        return new static($text);
    }

    public function __construct(protected string $text) {}

    public function addCard(Viber\Message\Button $button): self
    {
        $this->buttons[] = $button->toArray();
        return $this;
    }

    public function addButtons(array $buttons): self
    {
        foreach ($buttons as $card) {
            if ($card instanceof Viber\Message\Button) {
                $this->buttons[] = $card->toArray();
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            "type" => "rich_media",
            "rich_media" => $this->buttons
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
