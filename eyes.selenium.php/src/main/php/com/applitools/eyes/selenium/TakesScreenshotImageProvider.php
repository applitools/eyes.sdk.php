<?php
/**
 * An image provider based on WebDriver's {@link TakesScreenshot} interface.
 */
class TakesScreenshotImageProvider implements ImageProvider {

    private $logger; //Logger
    private $tsInstance; //TakesScreenshot

    public function __construct(Logger $logger, /*TakesScreenshot FIXME*/$tsInstance) {
        $this->logger = $logger;
        $this->tsInstance = $tsInstance;
    }

    public function getImage() {
        $this->logger->verbose("Getting screenshot as base64...");
        $screenshot64 = $this->tsInstance->getScreenshotAs(/*OutputType:: FIXME*/"BASE64");
        $this->logger->verbose("Done getting base64! Creating BufferedImage..");
        //FIXME don't need to convert from base64. Image was got

        return ImageUtils::imageFromBytes($screenshot64);
    }
}
