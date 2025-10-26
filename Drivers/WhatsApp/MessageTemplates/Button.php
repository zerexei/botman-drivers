<?php

namespace Drivers\WhatsApp\MessageTemplates;

class Button implements \JsonSerializable
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $text;

    /**
     * @param $text
     * @return static
     */
    public static function create($id, $text)
    {
        return new static($id, $text);
    }

    public function __construct($id, $text)
    {
        $this->id = $id;
        $this->text = $text;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            "id" => $this->id,
            "text" => $this->text,
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
