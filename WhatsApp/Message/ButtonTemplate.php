<?php

class ButtonTemplate implements \JsonSerializable
{
    /** @var string */
    protected $text;

    /** @var array */
    protected $buttons = [];

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
     * @param $button
     * @return $this
     */
    public function addButton(WhatsApp\Message\Button $button)
    {
        $this->buttons[] = $button->toArray();
        return $this;
    }

    /**
     * @param array $buttons
     * @return $this
     */
    public function addButtons(array $buttons)
    {
        foreach ($buttons as $button) {
            if ($button instanceof WhatsApp\Message\Button) {
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
            'type' => "interactive",
            'interactive' => [
                'type' => 'list',
                "body" => [
                    "text" => $this->text,
                ],
                'action' => [
                    "sections" => [
                        [
                            "title" => "Options",
                            "rows" => $this->buttons
                        ]
                    ],
                    "button" => "Options",
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
