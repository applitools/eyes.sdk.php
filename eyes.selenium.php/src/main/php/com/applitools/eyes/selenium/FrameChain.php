<?php
/**
 * Represents a path to a frame, including their location and scroll.
 */
class FrameChain /*implements Iterable<Frame>*/{
    private $logger; //Logger
    private $frames;

    /**
     * Compares two frame chains.
     * @param c1 Frame chain to be compared against c2.
     * @param c2 Frame chain to be compared against c1.
     * @return True if both frame chains represent the same frame,
     *         false otherwise.
     */
    public static function isSameFrameChain(FrameChain $c1, FrameChain $c2) {
        $lc1 = $c1->frames->size();
        $lc2 = $c2->frames->size();

        // different chains size means different frames
        if ($lc1 != $lc2) {
            return false;
        }

        /*Iterator<Frame>*/ $c1Iterator = $c1->iterator();
        /*Iterator<Frame>*/ $c2Iterator = $c2->iterator();

        //noinspection ForLoopReplaceableByForEach
        for($i = 0; $i<$lc1; ++$i) {
            if (!$c1Iterator->next()->getId()->equals($c2Iterator->next()->getId())) {
                return false;
            }
        }

        return true;
    }

    /**
     * Creates a frame chain which is a copy of the current frame.
     * @param logger A Logger instance.
     * @param other A frame chain from which the current frame chain will be
     *              created.
     */
    public function __construct(Logger $logger, FrameChain $other = null) {
        ArgumentGuard::notNull($logger, "logger");
        $this->logger = $logger;
        if(empty($other)){
            $this->frames = array();//new LinkedList<Frame>();
        }else{
            $logger->verbose(sprintf("Frame chain copy constructor (size %d)", $other->size()));
            foreach ($other->frames as $otherFrame) {
                $this->frames[] = new Frame($logger, $otherFrame->getReference(),
                    $otherFrame->getId(), $otherFrame->getLocation(),
                    $otherFrame->getSize(), $otherFrame->getParentScrollPosition());
            }
            $logger->verbose("Done!");
        }
    }

    /**
     *
     * @return The number of frames in the chain.
     */
    public function size() {
        return $this->frames->size();
    }

    /**
     * Removes all current frames in the frame chain.
     */
    public function clear() {
        $this->frames->clear();
    }

    /**
     * Removes the last inserted frame element. Practically means we switched
     * back to the parent of the current frame
     */
    public function pop() {
        $this->frames->remove($this->frames->size() - 1);
    }

    /**
     * Appends a frame to the frame chain.
     * @param frame The frame to be added.
     */
    public function push(Frame $frame) {
        $this->frames[] = frame;
    }

    /**
     *
     * @return The location of the current frame in the page.
     */
    public function getCurrentFrameOffset() {
        $result = new Location(0 ,0);
        foreach ($this->frames as $frame) {
            $result->offset($frame->getLocation());
        }
        return $result;
    }

    /**
     *
     * @return The outermost frame's location, or NoFramesException.
     */
    public function getDefaultContentScrollPosition() {
        if ($this->frames->size() == 0) {
            throw new NoFramesException("No frames in frame chain");
        }
        return new Location($this->frames->get(0)->getParentScrollPosition());
    }

    /**
     *
     * @return The size of the current frame.
     */
    public function getCurrentFrameSize() {
        $this->logger->verbose("getCurrentFrameSize()");
        $result = $this->frames->get($this->frames->size()-1)->getSize();
        $this->logger->verbose("Done!");
        return $result;
    }

    /**
     *
     * @return An iterator to go over the frames in the chain.
     */
    public /*Iterator<Frame>*/function iterator() {
      /*  return new Iterator<Frame>() {
            Iterator<Frame> framesIterator = frames.iterator();
            public boolean hasNext() {
                return framesIterator.hasNext();
            }

            public Frame next() {
                return framesIterator.next();
            }

            public void remove() {
                throw new EyesException(
                        "Remove is forbidden using the iterator!");
            }
        };*/ echo "MOCK4"; die();
    }
}
