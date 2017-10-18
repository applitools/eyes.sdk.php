<?php

namespace Applitools;


class TextTrigger extends Trigger
{
    /** @var Region */
    private $control;

    /** @var string */
    private $text;

    /**
     * TextTrigger constructor.
     * @param Region $control
     * @param string $text
     */
    public function __construct(Region $control, $text)
    {
        ArgumentGuard::notNull($control, "control");

        $this->control = $control;
        $this->text = $text;
    }

    public function getTriggerType()
    {
        return Trigger::Text;
    }

    public function getControl()
    {
        return $this->control;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    public function getAsFormattedArray()
    {
        return [
            "triggerType" => $this->getTriggerType(),
            "text" => $this->text,
            "control" => [
                "left" => $this->control->getLeft(),
                "top" => $this->control->getTop(),
                "width" => $this->control->getWidth(),
                "height" => $this->control->getHeight()
            ]
        ];
    }
}