<?php

namespace Drivers\Messenger\MessageTemplates;

class Element implements \JsonSerializable
{
    protected array $buttons;

    public static function create($title, $subtitle, $imageUrl): static
    {
        return new static($title, $subtitle, $imageUrl);
    }

    public function __construct(
        protected string $title,
        protected string $subtitle,
        protected string $imageUrl
    ) {}

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

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            "title" => $this->title,
            "subtitle" => $this->subtitle,
            "image_url" => $this->imageUrl,
            "buttons" => $this->buttons,
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
