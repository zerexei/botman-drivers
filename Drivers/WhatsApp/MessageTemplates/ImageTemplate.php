<?php

namespace Drivers\WhatsApp\MessageTemplates;

class ImageTemplate implements \JsonSerializable
{
    /** @var string */
    protected $text;

    /**
     * @param $text
     * @return static
     */
    public static function create($text)
    {
        return new static($text);
    }

    public function __construct($text)
    {
        $this->text = $text;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            "type" => "image",
            "image" => [
                "link" => $this->text,
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
