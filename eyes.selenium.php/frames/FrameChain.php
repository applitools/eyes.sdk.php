<?php

namespace Applitools\Selenium;

use Applitools\ArgumentGuard;
use Applitools\Location;
use Applitools\Logger;
use Applitools\RectangleSize;
use Applitools\Selenium\Exceptions\NoFramesException;

/**
 * Represents a path to a frame, including their location and scroll.
 */
class FrameChain
{
    /** @var Logger */
    private $logger;

    /** @var Frame[] */
    private $frames;

    /**
     * Compares two frame chains.
     * @param FrameChain $c1 Frame chain to be compared against c2.
     * @param FrameChain $c2 Frame chain to be compared against c1.
     * @return bool True if both frame chains represent the same frame, false otherwise.
     */
    public static function isSameFrameChain(FrameChain $c1, FrameChain $c2)
    {
        $lc1 = count($c1->frames);
        $lc2 = count($c2->frames);

        // different chains size means different frames
        if ($lc1 != $lc2) {
            return false;
        }

        for ($i = 0; $i < $lc1; ++$i) {
            if (!($c1->frames[$i]->equals($c2->frames[$i]))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Creates a frame chain which is a copy of the current frame.
     * @param Logger $logger A Logger instance.
     * @param FrameChain $other A frame chain from which the current frame chain will be created.
     */
    public function __construct(Logger $logger, FrameChain $other = null)
    {
        ArgumentGuard::notNull($logger, "logger");
        $this->logger = $logger;
        $this->frames = [];
        if (!empty($other)) {
            $logger->verbose("Frame chain copy constructor (size {$other->size()})");

            foreach ($other->frames as $otherFrame) {
                $this->frames[] = clone $otherFrame;
                /*new Frame($logger, $otherFrame->getReference(), $otherFrame->getLocation(),
                    $otherFrame->getSize(),  $otherFrame->getInnerSize(),
                    $otherFrame->getParentScrollPosition(), $otherFrame->getOriginalLocation());*/
            }
            $logger->verbose("Done!");
        }
    }

    /**
     *
     * @return int The number of frames in the chain.
     */
    public function size()
    {
        return count($this->frames);
    }

    /**
     * Removes all current frames in the frame chain.
     */
    public function clear()
    {
        $this->frames = [];
    }

    /**
     * Removes the last inserted frame element. Practically means we switched
     * back to the parent of the current frame
     * @return Frame|null Returns the popped frame
     */
    public function pop()
    {
        return array_pop($this->frames);
    }

    /**
     * Appends a frame to the frame chain.
     * @param Frame $frame The frame to be added.
     */
    public function push(Frame $frame)
    {
        $this->frames[] = $frame;
    }

    /**
     * @return Frame|null Returns the current frame
     */
    public function peek()
    {
        if (count($this->frames) == 0) {
            return null;
        }
        return $this->frames[count($this->frames) - 1];
    }

    /**
     *
     * @return Location The location of the current frame in the page.
     */
    public function getCurrentFrameOffset()
    {
        $result = new Location(0, 0);
        foreach ($this->frames as $frame) {
            $loc = $frame->getLocation();
            $result->offset($loc->getX(), $loc->getY());
        }
        return $result;
    }

    /**
     * @return Location The outermost frame's location, or NoFramesException.
     * @throws NoFramesException
     */
    public function getDefaultContentScrollPosition()
    {
        if (count($this->frames) == 0) {
            throw new NoFramesException("No frames in frame chain");
        }
        return clone ($this->frames[0]->getParentScrollPosition());
    }

    /**
     *
     * @return RectangleSize The size of the current frame.
     */
    public function getCurrentFrameSize()
    {
        $this->logger->verbose("getCurrentFrameSize()");
        $result = $this->frames[count($this->frames) - 1]->getSize();
        $this->logger->verbose("Done!");
        return $result;
    }

    /**
     * @return RectangleSize The inner size of the current frame.
     */
    public function getCurrentFrameInnerSize()
    {
        $this->logger->verbose("GetCurrentFrameInnerSize()");
        $result = $this->frames[count($this->frames) - 1]->getInnerSize();
        $this->logger->verbose("Done!");
        return $result;
    }

    /**
     *
     * @return Frame[] The array of frames in this chain.
     */
    public function getFrames()
    {
        $this->logger->verbose("getFrames()");
        return $this->frames;
    }
}
