<?php

namespace Drivers\Viber\MessageTemplates;

class Element implements \JsonSerializable
{
    protected array $buttons;

    public static function create($title, $subtitle, $imageUrl): static
    {
        return new static($title, $subtitle, $imageUrl);
    }

    public function __construct(
        protected string $title = "",
        protected string $subtitle = "",
        protected string $imageUrl = "",

    ) {}

    public function addButton(\Drivers\Viber\MessageTemplates\Button $button): self
    {
        $this->buttons[] = $button->toArray();

        return $this;
    }

    public function addButtons(array $buttons): self
    {
        foreach ($buttons as $button) {
            if ($button instanceof \Drivers\Viber\MessageTemplates\Button) {
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
        $payload = [];

        $payload[] =  [
            'Image' => $this->imageUrl,
            'ActionType' => 'open-url',
            'ActionBody' => '#',
            'Silent' => true,
            'Rows' => 3,
        ];

        $payload[] = [
            'ActionType' => 'open-url',
            'ActionBody' => '#',
            'Silent' => true,
            'Text' => '<font size="12">' . $this->title . ' <br> ' . $this->subtitle . ' </font>',
            'Rows' => 2,
        ];

        if (!empty($this->buttons)) {
            array_push($payload, ...$this->buttons);
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
