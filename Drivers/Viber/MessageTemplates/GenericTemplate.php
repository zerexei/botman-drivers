<?php

namespace Drivers\Viber\MessageTemplates;

class GenericTemplate implements \JsonSerializable
{
    protected array $elements = [];

    public static function create(): static
    {
        return new static();
    }

    public function __construct() {}

    public function addElement(\Drivers\Viber\MessageTemplates\Element $element): self
    {
        array_push($this->elements, ...$element->toArray());
        return $this;
    }

    public function addElements(array $elements): self
    {
        foreach ($elements as $element) {
            if ($element instanceof \Drivers\Viber\MessageTemplates\Element) {
                array_push($this->elements, ...$element->toArray());
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            "type" => "rich_media",
            "rich_media" => $this->elements
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
