<?php

namespace Applitools;

/**
 * Encapsulates a result for the CompareAndCopyBlockChannelData function.
 */
class CompareAndCopyBlockChannelDataResult
{
    private $isIdentical;
    private $buffer;

    /**
     *
     * @param bool $isIdentical Whether or not the target block was identical to the source block.
     * @param int[] $buffer The target block's pixel values for a specific channel.
     */
    public function __construct($isIdentical, $buffer)
    {
        $this->isIdentical = $isIdentical;
        $this->buffer = $buffer;
    }

    /**
     * @return bool Whether or not the target block was identical to the source block.
     */
    public function getIsIdentical()
    {
        return $this->isIdentical;
    }

    /**
     * @return int[] The target block's pixel values for a specific channel.
     */
    public function getBuffer()
    {
        return $this->buffer;
    }
}

?>