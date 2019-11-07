<?php

namespace Applitools\Images;

use Applitools\Exceptions\TestFailedException;
use Applitools\EyesBase;
use Applitools\fluent\CheckSettings;
use Applitools\RectangleSize;
use Applitools\ArgumentGuard;
use Applitools\RegionProvider;
use Applitools\EyesImagesScreenshot;
use Applitools\Region;
use Applitools\CoordinatesType;
use Applitools\Location;

class Eyes extends EyesBase
{

    /** @var string */
    private $title;

    /** @var EyesImagesScreenshot */
    private $screenshot;

    /** @var bool */
    private $inferred;

    /**
     * Creates a new (possibly disabled) Eyes instance that interacts
     * with the Eyes Server at the specified url.
     *
     * @param string $serverUrl The Eyes server URL.
     */
    public function __construct($serverUrl = null)
    {
        if (empty($serverUrl)) {
            parent::__construct($this->getDefaultServerUrl());
        } else {
            parent::__construct($serverUrl);
        }
    }

    public function getBaseAgentId()
    {
        return "eyes.images.php/{$this->getVersion()}";
    }


    private function guessType($filename)
    {
        if (function_exists('exif_imagetype')) {
            $this->logger->verbose("using exif_imagetype...");
            $type = @exif_imagetype($filename);
            if (false !== $type) {
                if ($type == IMAGETYPE_JPEG) {
                    return 'jpeg';
                }
                if ($type == IMAGETYPE_GIF) {
                    return 'gif';
                }
                if ($type == IMAGETYPE_PNG) {
                    return 'png';
                }
            }
        }

        $this->logger->verbose("exif_imagetype not found. getting extension.");

        $parts = explode('.', $filename);
        $ext = strtolower($parts[count($parts) - 1]);
        $this->logger->verbose("extension: $ext");
        if (strcasecmp($ext, 'png') == 0) {
            return 'png';
        } else if (strcasecmp($ext, 'gif') == 0) {
            return 'gif';
        }
        return 'jpeg';
    }

    /**
     * Starts a test.
     *
     * @param string $appName The name of the application under test.
     * @param string $testName The test name.
     * @param RectangleSize $dimensions Determines the resolution used for the baseline.
     *                       {@code null} will automatically grab the
     *                       resolution from the image.
     * @throws \Applitools\Exceptions\EyesException
     * @throws \Exception
     */
    public function open($appName, $testName, RectangleSize $dimensions = null)
    {
        $this->openBase($appName, $testName, $dimensions, null);
    }

    /**
     * @param resource $image The image to perform visual validation for.
     * @param string $tag An optional tag to be associated with the snapshot.
     * @param bool $ignoreMismatch Whether to ignore this check if a mismatch is found.
     * @return bool True if the image matched the expected output, false otherwise.
     * @throws TestFailedException
     */
    public function checkWindow($image, $tag = null, $ignoreMismatch = null)
    {
        return $this->checkImage($image, $tag, $ignoreMismatch);
    }

    /**
     * Matches the input image with the next expected image.
     *
     * @param resource|string $image The image path or the image to perform visual validation for.
     * @param string $tag An optional tag to be associated with the validation checkpoint.
     * @param bool $ignoreMismatch True if the server should ignore a negative result for the visual validation.
     * @return bool True if the image matched the expected output, false otherwise.
     * @throws TestFailedException Thrown if a mismatch is detected and immediate failure reports are enabled.
     */
    public function checkImage($image, $tag = null, $ignoreMismatch = false)
    {
        if (is_string($image)) {
            $type = $this->guessType($image);
            $this->logger->verbose("CheckImage($image, '$tag', $ignoreMismatch): guessed image type: $type");

            if ($type == 'png') {
                $image = imagecreatefrompng($image);
            } else if ($type == 'gif') {
                $image = imagecreatefromgif($image);
            } else {
                $image = imagecreatefromjpeg($image);
            }
        }
        if ($this->getIsDisabled()) {
            $this->logger->verbose("CheckImage(Image, '$tag', $ignoreMismatch): Ignored");
            return false;
        }
        ArgumentGuard::notNull($image, "image cannot be null!");

        $this->logger->verbose("CheckImage(Image, '$tag', $ignoreMismatch)");

        if ($this->viewportSize == null) {
            $this->setViewportSize(
                new RectangleSize(imagesx($image), imagesy($image))
            );
        }

        return $this->checkImage_(new RegionProvider(), $image, $tag, $ignoreMismatch);
    }

    /**
     * Perform visual validation for the current image.
     *
     * @param resource|string $image The image to perform visual validation for.
     * @param Region $region The region to validate within the image.
     * @param string $tag An optional tag to be associated with the validation checkpoint.
     * @param bool $ignoreMismatch True if the server should ignore a negative result for the visual validation.
     * @throws TestFailedException Thrown if a mismatch is detected and immediate failure reports are enabled.
     * @return bool Whether or not the image matched the baseline.
     */
    public function checkRegion($image, Region $region, $tag = null, $ignoreMismatch = false)
    {
        if ($this->getIsDisabled()) {
            $this->logger->verbose(sprintf(
                "CheckRegion(Image, [%s], '%s', %b): Ignored",
                $region, $tag, $ignoreMismatch));
            return false;
        }
        ArgumentGuard::notNull($image, "image cannot be null!");
        ArgumentGuard::notNull($region, "region cannot be null!");

        if (is_string($image)) {
            $type = $this->guessType($image);
            if ($type == 'png') {
                $image = imagecreatefrompng($image);
            } else if ($type == 'gif') {
                $image = imagecreatefromgif($image);
            } else {
                $image = imagecreatefromjpeg($image);
            }
        }

        $this->logger->verbose(sprintf("CheckRegion(Image, [%s], '%s', %b)",
            $region, $tag, $ignoreMismatch));

        if ($this->viewportSize == null) {
            $this->setViewportSize(
                new RectangleSize(imagesx($image), imagesy($image))
            );
        }

        $regionProvider = new RegionProvider($region);
        $regionProvider->setCoordinatesType(CoordinatesType::SCREENSHOT_AS_IS);

        return $this->checkImage_($regionProvider, $image, $tag, $ignoreMismatch);
    }


    /**
     * Adds a mouse trigger.
     *
     * @param string $action Mouse action.
     * @param Region $control The control on which the trigger is activated (context relative coordinates).
     * @param Location $cursor The cursor's position relative to the control.
     */
    public function addMouseTriggerCursor($action, Region $control, Location $cursor)
    {
        $this->addMouseTriggerBase($action, $control, $cursor);
    }

    /**
     * Adds a keyboard trigger.
     *
     * @param Region $control The control's context-relatieve region.
     * @param string $text The trigger's text.
     */
    public function addTextTrigger(Region $control, $text)
    {
        $this->addTextTriggerBase($control, $text);
    }

    /**
     * @return RectangleSize The viewport size of the AUT.
     */
    public function getViewportSize()
    {
        return $this->viewportSize;
    }

    /**
     * @param RectangleSize $size The required viewport size.
     */
    protected function setViewportSize(RectangleSize $size)
    {
        ArgumentGuard::notNull($size, "size");
        $this->viewportSize = new RectangleSize($size->getWidth(),
            $size->getHeight());
    }

    /**
     * @return string The inferred environment string
     */
    protected function getInferredEnvironment()
    {
        return $this->inferred != null ? $this->inferred : "";
    }

    /**
     * Sets the inferred environment for the test.
     * @param string $inferred The inferred environment string.
     */
    public function setInferredEnvironment($inferred)
    {
        $this->inferred = $inferred;
    }

    public function getScreenshot()
    {
        return $this->screenshot;
    }

    /**
     * @return string The current title of of the AUT.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * See {@link #checkImage_(RegionProvider, String, boolean)}.
     *
     * @param RegionProvider $regionProvider The region for which verification will be performed. see
     *                       {@link #checkWindowBase(RegionProvider, String, boolean, int)}.
     * @param resource $image The image to perform visual validation for.
     * @param string $tag An optional tag to be associated with the validation checkpoint.
     * @param bool $ignoreMismatch True if the server should ignore a negative result for the visual validation.
     * @return bool True if the image matched the expected output, false otherwise.
     * @throws TestFailedException
     */
    private function checkImage_(RegionProvider $regionProvider, $image, $tag, $ignoreMismatch)
    {
        // Set the screenshot to be verified.
        $this->screenshot = new EyesImagesScreenshot($image);

        // Set the title to be linked to the screenshot.
        $this->title = ($tag != null) ? $tag : "";

        $mr = $this->checkWindowBase($regionProvider, $tag, $ignoreMismatch, null);

        return $mr->getAsExpected();
    }
}
