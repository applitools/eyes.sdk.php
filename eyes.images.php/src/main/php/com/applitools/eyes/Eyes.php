<?php

namespace Applitools\Images;
use Gregwar\Image\Image;
use Applitools\EyesBase;
use Applitools\RectangleSize;
use Applitools\ArgumentGuard;
use Applitools\RegionProvider;
use Applitools\EyesImagesScreenshot;

class Eyes extends EyesBase {

    private $title;
    private $screenshot; //EyesImagesScreenshot
    private $inferred;

    /**
     * Creates a new (possibly disabled) Eyes instance that interacts
     * with the Eyes Server at the specified url.
     *
     * @param string $serverUrl  The Eyes server URL.
     */
    public function __construct($serverUrl = null) {
        if(empty($serverUrl)){
            parent::__construct($this->getDefaultServerUrl());
        }else{
            parent::__construct($serverUrl);
        }
    }

    public function getBaseAgentId() {
        return "eyes.images.php/0.1";
    }

    /**
     * Starts a test.
     *
     * @param string $appName        The name of the application under test.
     * @param string $testName       The test name.
     * @param RectangleSize $dimensions      Determines the resolution used for the baseline.
     *                       {@code null} will automatically grab the
     *                       resolution from the image.
     */
    public function open($appName, $testName,
            RectangleSize $dimensions = null) {
        $this->openBase($appName, $testName, $dimensions, null);
    }

    /**

     * @param Image $image The image to perform visual validation for.
     * @param string $tag An optional tag to be associated with the snapshot.
     * @param bool $ignoreMismatch Whether to ignore this check if a mismatch is found.
     */
    public function checkWindow(Image $image, $tag = null, $ignoreMismatch = null) {
        return $this->checkImage($image, $tag, $ignoreMismatch);
    }

    /**
     * Matches the input image with the next expected image.
     *
     * @param $image The image path or the image to perform visual validation for.
     * @param string $tag An optional tag to be associated with the validation
     *            checkpoint.
     * @param bool $ignoreMismatch True if the server should ignore a negative
     *                       result for the visual validation.
     * @return True if the image matched the expected output, false otherwise.
     * @throws TestFailedException Thrown if a mismatch is detected and
     *                              immediate failure reports are enabled.
     */
    public function checkImage($image, $tag = null, $ignoreMismatch = false) {
        if(!$image instanceof Image){
            $image = Image::open($image);
        }
        if ($this->getIsDisabled()) {
            $this->logger->verbose(sprintf("CheckImage(Image, '%s', %b): Ignored", $tag, $ignoreMismatch));
            return false;
        }
        ArgumentGuard::notNull($image, "image cannot be null!");

        $this->logger->verbose(sprintf("CheckImage(Image, '%s', %b)", $tag, $ignoreMismatch));

        if ($this->viewportSize == null) {
            $this->setViewportSize(
                    new RectangleSize($image->getWidth(), $image->getHeight())
            );
        }

        return $this->checkImage_(new RegionProvider(), $image, $tag, $ignoreMismatch);
    }

    /**
     * Perform visual validation for the current image.
     *
     * @param Image $image The image to perform visual validation for.
     * @param Region $region The region to validate within the image.
     * @param string $tag An optional tag to be associated with the validation
     *            checkpoint.
     * @param bool $ignoreMismatch True if the server should ignore a negative
     *                       result for the visual validation.
     * @throws TestFailedException Thrown if a mismatch is detected and
     *                              immediate failure reports are enabled.
     * @return Whether or not the image matched the baseline.
     */
    public function checkRegion(Image $image, Region $region,
                               $tag =null, $ignoreMismatch = false) {
        if ($this->getIsDisabled()) {
            $this->logger->verbose(sprintf(
                    "CheckRegion(Image, [%s], '%s', %b): Ignored",
                    $region, $tag, $ignoreMismatch));
            return false;
        }
        ArgumentGuard::notNull($image, "image cannot be null!");
        ArgumentGuard::notNull($region, "region cannot be null!");

        $this->logger->verbose(sprintf("CheckRegion(Image, [%s], '%s', %b)",
                $region, $tag, $ignoreMismatch));

        if ($this->viewportSize == null) {
            $this->setViewportSize(
                    new RectangleSize($image->width(), $image->height())
            );
        }

        $regionProvider = new RegionProvider($region);
        $regionProvider->setCoordinatesType(CoordinatesType.SCREENSHOT_AS_IS);

        return $this->checkImage_($regionProvider, $image, $tag, $ignoreMismatch);
    }


    /**
     * Adds a mouse trigger.
     *
     * @param $action  Mouse action.
     * @param $control The control on which the trigger is activated (context
     *                relative coordinates).
     * @param $cursor  The cursor's position relative to the control.
     */
    public function addMouseTrigger(MouseAction $action, Region $control,
            Location $cursor) {
        $this->addMouseTriggerBase($action, $control, $cursor);
    }

    /**
     * Adds a keyboard trigger.
     *
     * @param $control The control's context-relatieve region.
     * @param $text    The trigger's text.
     */
    public function addTextTrigger(Region $control, $text) {
        $this->addTextTriggerBase($control, $text);
    }

    /**
     * @return RectangleSize The viewport size of the AUT.
     */
    public function getViewportSize() {
        return $this->viewportSize;
    }
    
    /**
     * @param RectangleSize $size The required viewport size.
     */
    protected function setViewportSize(RectangleSize $size) {
        ArgumentGuard::notNull($size, "size");
        $this->viewportSize = new RectangleSize($size->getWidth(),
                $size->getHeight());
    }

    /**
     * @return string The inferred environment string
     */
    protected function getInferredEnvironment() {
        return $this->inferred != null ? $this->inferred : "";
    }

    /**
     * Sets the inferred environment for the test.
     * @param $inferred The inferred environment string.
     */
    public function setInferredEnvironment($inferred) {
        $this->inferred = $inferred;
    }

    public function getScreenshot() {
        return $this->screenshot;
    }

    /**
     * @return string The current title of of the AUT.
     */
    protected function getTitle() {
        return $this->title;
    }

    /**
     * See {@link #checkImage_(RegionProvider, String, boolean)}.
     *
     * @param $regionProvider The region for which verification will be
     *                       performed. see
     *                       {@link #checkWindowBase(RegionProvider, String,
     *                       boolean, int)}.
     * @param $image The image to perform visual validation for.
     * @param $tag An optional tag to be associated with the validation
     *            checkpoint.
     * @param $ignoreMismatch True if the server should ignore a negative
     *                       result for the visual validation.
     * @return True if the image matched the expected output, false otherwise.
     */
    private function checkImage_(RegionProvider $regionProvider,
                                Image $image,
                                $tag,
                                $ignoreMismatch) {

        // Set the screenshot to be verified.
        $this->screenshot = new EyesImagesScreenshot($image);

         // Set the title to be linked to the screenshot.
        $this->title = ($tag != null) ? $tag : "";

        $mr = $this->checkWindowBase($regionProvider, $tag,
                $ignoreMismatch);

        return $mr->getAsExpected();
    }
}
