<?php

namespace Applitools\Selenium;

use Applitools\ImageProvider;
use Applitools\ImageUtils;
use Applitools\Logger;
use Applitools\Region;

class SafariScreenshotImageProvider implements ImageProvider
{
    /** @var Eyes */
    private $eyes;

    /** @var Logger */
    private $logger;

    /** @var UserAgent */
    private $userAgent;

    /** @var EyesWebDriver */
    private $tsInstance;

    /** @var Region[] */
    private static $devicesRegions = null;

    function __construct(Eyes $eyes, Logger $logger, EyesWebDriver $tsInstance, UserAgent $userAgent)
    {
        $this->eyes = $eyes;
        $this->logger = $logger;
        $this->tsInstance = $tsInstance;
        $this->userAgent = $userAgent;
    }

    /**
     * @return resource
     * @throws Exceptions\NoFramesException
     * @throws Exceptions\EyesDriverOperationException
     */
    function getImage()
    {
        $this->logger->verbose("Getting screenshot...");
        $image = $this->tsInstance->getScreenshot();
        $this->eyes->getDebugScreenshotsProvider()->save($image, "SAFARI");

        if ($this->eyes->getIsCutProviderExplicitlySet()) {
            return $image;
        }

        $scaleRatio = $this->eyes->getDevicePixelRatio();
        $originalViewportSize = $this->eyes->getViewportSize();
        $viewportSize = $originalViewportSize->scale($scaleRatio);

        $this->logger->verbose("logical viewport size: $originalViewportSize");

        if (strcasecmp($this->userAgent->getOS(), OSNames::IOS) == 0) {
            if (self::$devicesRegions == null) {
                $this->initDeviceRegionsTable();
            }

            $imageWidth = imagesx($image);
            $imageHeight = imagesy($image);

            $this->logger->verbose("physical device pixel size: $imageWidth x $imageHeight");

            $key = "{$imageWidth}, {$imageHeight}, {$originalViewportSize->getWidth()}, {$originalViewportSize->getHeight()}, {$this->userAgent->getBrowserMajorVersion()}";
            if (isset(self::$devicesRegions[$key])) {
                $this->logger->verbose("device data found in hash table");
                $crop = self::$devicesRegions[$key];
                $image = ImageUtils::getImagePart($image, $crop);
                //$image = $image->crop($crop->getLeft(), $crop->getTop(), $crop->getWidth(), $crop->getHeight());
            } else {
                $this->logger->verbose("device not found in list. returning original image.");
            }
        } else if (!$this->eyes->getForceFullPageScreenshot()) {

            $currentFrameChain = $this->eyes->getDriver()->getFrameChain();

            if ($currentFrameChain->size() == 0) {
                $positionProvider = new ScrollPositionProvider($this->logger, $this->tsInstance);
                $loc = $positionProvider->getCurrentPosition();
            } else {
                $loc = $currentFrameChain->getDefaultContentScrollPosition();
            }

            $loc = $loc->scale($scaleRatio);
            $image = imagecrop($image, ['x' => $loc->getX(), 'y' => $loc->getY(), 'width' => $viewportSize->getWidth(), 'height' => $viewportSize->getHeight()]);
            //$image = $image->crop($loc->getX(), $loc->getY(), $viewportSize->getWidth(), $viewportSize->getHeight());
        }

        return $image;
    }

    private function initDeviceRegionsTable()
    {
        self::$devicesRegions = [];

        self::$devicesRegions["1125, 2436, 375, 635, 11"] = Region::CreateFromLTWH(0, 283, 1125, 1903);
        self::$devicesRegions["2436, 1125, 724, 325, 11"] = Region::CreateFromLTWH(132, 151, 2436, 930);

        self::$devicesRegions["1242, 2208, 414, 622, 11"] = Region::CreateFromLTWH(0, 211, 1242, 1863);
        self::$devicesRegions["2208, 1242, 736, 364, 11"] = Region::CreateFromLTWH(0, 151, 2208, 1090);

        self::$devicesRegions["1242, 2208, 414, 628, 10"] = Region::CreateFromLTWH(0, 193, 1242, 1882);
        self::$devicesRegions["2208, 1242, 736, 337, 10"] = Region::CreateFromLTWH(0, 231, 2208, 1010);

        self::$devicesRegions["750, 1334, 375, 553, 11"] = Region::CreateFromLTWH(0, 141, 750, 1104);
        self::$devicesRegions["1334, 750, 667, 325, 11"] = Region::CreateFromLTWH(0, 101, 1334, 648);

        self::$devicesRegions["750, 1334, 375, 559, 10"] = Region::CreateFromLTWH(0, 129, 750, 1116);
        self::$devicesRegions["1334, 750, 667, 331, 10"] = Region::CreateFromLTWH(0, 89, 1334, 660);

        self::$devicesRegions["640, 1136, 320, 460, 10"] = Region::CreateFromLTWH(0, 129, 640, 918);
        self::$devicesRegions["1136, 640, 568, 232, 10"] = Region::CreateFromLTWH(0, 89, 1136, 462);

        self::$devicesRegions["1536, 2048, 768, 954, 11"] = Region::CreateFromLTWH(0, 141, 1536, 1907);
        self::$devicesRegions["2048, 1536, 1024, 698, 11"] = Region::CreateFromLTWH(0, 141, 2048, 1395);

        self::$devicesRegions["1536, 2048, 768, 922, 11"] = Region::CreateFromLTWH(0, 206, 1536, 1842);
        self::$devicesRegions["2048, 1536, 1024, 666, 11"] = Region::CreateFromLTWH(0, 206, 2048, 1330);

        self::$devicesRegions["1536, 2048, 768, 960, 10"] = Region::CreateFromLTWH(0, 129, 1536, 1919);
        self::$devicesRegions["2048, 1536, 1024, 704, 10"] = Region::CreateFromLTWH(0, 129, 2048, 1407);

        self::$devicesRegions["1536, 2048, 768, 928, 10"] = Region::CreateFromLTWH(0, 194, 1536, 1854);
        self::$devicesRegions["2048, 1536, 1024, 672, 10"] = Region::CreateFromLTWH(0, 194, 2048, 1342);

        self::$devicesRegions["2048, 2732, 1024, 1296, 11"] = Region::CreateFromLTWH(0, 141, 2048, 2591);
        self::$devicesRegions["2732, 2048, 1366, 954, 11"] = Region::CreateFromLTWH(0, 141, 2732, 1907);

        self::$devicesRegions["1668, 2224, 834, 1042, 11"] = Region::CreateFromLTWH(0, 141, 1668, 2083);
        self::$devicesRegions["2224, 1668, 1112, 764, 11"] = Region::CreateFromLTWH(0, 141, 2224, 1527);
    }

}