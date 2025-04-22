<?php

class TextTemplate implements \JsonSerializable
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
            "type" => "text",
            "text" => [
                "preview_url" => false,
                "body" => $this->text,
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
