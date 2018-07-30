<?php
/**
 * Applitools software
 */


namespace Tests\Applitools\Selenium;

use Applitools\FileLogger;
use Applitools\PrintLogHandler;
use Applitools\Selenium\Eyes;
use Applitools\Selenium\fluent\Target;
use Applitools\Selenium\StitchMode;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\TestCase;

require_once('TestDataProvider.php');

class TestIOSDevices extends TestCase
{

    private $logsPath = ".";

    public function data()
    {
        return [
//                ["iPhone X Simulator", "11.2", "portrait", false],
//                ["iPhone X Simulator", "11.2", "landscape", false],
//                ["iPhone 7 Simulator", "11.0", "portrait", false],
//                ["iPhone 7 Simulator", "11.0", "landscape", false],
//                ["iPhone 7 Simulator", "10.0", "portrait", false],
//                ["iPhone 7 Simulator", "10.0", "landscape", false],
//                ["iPhone 6 Plus Simulator", "11.0", "portrait", false],
//                ["iPhone 6 Plus Simulator", "11.0", "landscape", false],
//                ["iPhone 6 Plus Simulator", "10.0", "portrait", false],
//                ["iPhone 6 Plus Simulator", "10.0", "landscape", false],
//                ["iPhone 5s Simulator", "10.0", "portrait", false],
//                ["iPhone 5s Simulator", "10.0", "landscape", false],
//                ["iPad Simulator", "11.0", "portrait", false],
//                ["iPad Simulator", "11.0", "landscape", false],
//                ["iPad Pro (9.7 inch) Simulator", "11.0", "portrait", false],
//                ["iPad Pro (9.7 inch) Simulator", "11.0", "landscape", false],
//                ["iPad Pro (12.9 inch) Simulator", "11.0", "portrait", false],
//                ["iPad Pro (12.9 inch) Simulator", "11.0", "landscape", false],
//                ["iPad Pro (12.9 inch) (2nd generation) Simulator", "11.0", "portrait", false],
//                ["iPad Pro (12.9 inch) (2nd generation) Simulator", "11.0", "landscape", false],
//                ["iPad Pro (10.5 inch) Simulator", "11.0", "portrait", false],
//                ["iPad Pro (10.5 inch) Simulator", "11.0", "landscape", false],
//                ["iPad (5th generation) Simulator", "11.0", "portrait", false],
//                ["iPad (5th generation) Simulator", "11.0", "landscape", false],
//                ["iPad Air Simulator", "11.0", "portrait", false],
//                ["iPad Air Simulator", "11.0", "landscape", false],
//                ["iPad Air 2 Simulator", "11.0", "portrait", false],
//                ["iPad Air 2 Simulator", "11.0", "landscape", false],
//                ["iPhone X Simulator", "11.2", "portrait", true],
//                ["iPhone X Simulator", "11.2", "landscape", true],
//                ["iPhone 7 Simulator", "11.0", "portrait", true],
//                ["iPhone 7 Simulator", "11.0", "landscape", true],
//                ["iPhone 7 Simulator", "10.0", "portrait", true],
//                ["iPhone 7 Simulator", "10.0", "landscape", true],
//                ["iPhone 6 Plus Simulator", "11.0", "portrait", true],
//                ["iPhone 6 Plus Simulator", "11.0", "landscape", true],
//                ["iPhone 6 Plus Simulator", "10.0", "portrait", true],
//                ["iPhone 6 Plus Simulator", "10.0", "landscape", true],
//                ["iPhone 5s Simulator", "10.0", "portrait", true],
//                ["iPhone 5s Simulator", "10.0", "landscape", true],
//                ["iPad Simulator", "11.0", "portrait", true],
//                ["iPad Simulator", "11.0", "landscape", true],
//                ["iPad Pro (9.7 inch) Simulator", "11.0", "portrait", true],
//                ["iPad Pro (9.7 inch) Simulator", "11.0", "landscape", true],
//                ["iPad Pro (12.9 inch) Simulator", "11.0", "portrait", true],
//                ["iPad Pro (12.9 inch) Simulator", "11.0", "landscape", true],
//                ["iPad Pro (12.9 inch) (2nd generation) Simulator", "11.0", "portrait", true],
//                ["iPad Pro (12.9 inch) (2nd generation) Simulator", "11.0", "landscape", true],
//                ["iPad Pro (10.5 inch) Simulator", "11.0", "portrait", true],
//                ["iPad Pro (10.5 inch) Simulator", "11.0", "landscape", true],
//                ["iPad (5th generation) Simulator", "11.0", "portrait", true],
//                ["iPad (5th generation) Simulator", "11.0", "landscape", true],
//                ["iPad Air Simulator", "11.0", "portrait", true],
//                ["iPad Air Simulator", "11.0", "landscape", true],
//                ["iPad Air 2 Simulator", "11.0", "portrait", true],
//                ["iPad Air 2 Simulator", "11.0", "landscape", true]
            ["iPhone 5s", "10.3", "portrait", true],
        ];
    }

    /**
     * @test
     * @dataProvider data
     * @doesNotPerformAssertions
     * @param string $deviceName
     * @param string $platformVersion
     * @param string $deviceOrientation
     * @param boolean $fully
     * @throws \Applitools\Exceptions\EyesException
     * @throws \Applitools\Exceptions\NewTestException
     * @throws \Applitools\Exceptions\TestFailedException
     * @throws \Exception
     */
    public function TestIOSSafariCrop($deviceName, $platformVersion, $deviceOrientation, $fully)
    {
        $caps = new DesiredCapabilities();

        $eyes = $this->initEyes($deviceName, $platformVersion, $deviceOrientation, $fully, $caps);

        $testName = "$deviceName $platformVersion $deviceOrientation";
        if ($fully) {
            $testName .= " fully";
        }

        $caps->setCapability("name", "$testName ({$eyes->getFullAgentId()})");
        $caps->setCapability("browserName", "Safari");
        $caps->setCapability("newCommandTimeout", 600);

        //$appiumUrl = "http://ondemand.saucelabs.com/wd/hub";
        $appiumUrl = "http://192.168.1.197:4723/wd/hub";
        $driver = RemoteWebDriver::create($appiumUrl, $caps, 300000, 300000);

        try {
            $this->initLogging($eyes, $testName);
            $driver->get("https://www.applitools.com/customers");
            $eyes->open($driver, "Eyes Selenium SDK - iOS Safari Cropping", $testName);
            $eyes->check("Initial view", Target::window()->fully($fully));
            $eyes->close();
        } finally {
            $eyes->abortIfNotClosed();
            $driver->quit();
        }
    }

    /**
     * @test
     * @dataProvider data
     * @doesNotPerformAssertions
     * @param string $deviceName
     * @param string $platformVersion
     * @param string $deviceOrientation
     * @param boolean $fully
     * @throws \Applitools\Exceptions\EyesException
     * @throws \Applitools\Exceptions\NewTestException
     * @throws \Applitools\Exceptions\TestFailedException
     * @throws \Exception
     */
    public function TestIOSNativeApp($deviceName, $platformVersion, $deviceOrientation, $fully)
    {
        $caps = new DesiredCapabilities();

        $eyes = $this->initEyes($deviceName, $platformVersion, $deviceOrientation, $fully, $caps);

        $testName = "$deviceName Native $platformVersion $deviceOrientation";
        if ($fully) {
            $testName .= " fully";
        }

        $caps->setCapability("name", "$testName ({$eyes->getFullAgentId()})");
        $caps->setCapability("app", "https://store.applitools.com/download/iOS.TestApp.app.zip");

        $sauceUrl = "http://ondemand.saucelabs.com/wd/hub";
        $driver = RemoteWebDriver::create($sauceUrl, $caps, null, 240000);

        try {
            $this->initLogging($eyes, $testName);
            $eyes->open($driver, "Eyes Selenium SDK - iOS Safari Native App", $testName);
            $eyes->checkWindow("Native App");
            $eyes->close();
        } finally {
            $eyes->abortIfNotClosed();
            $driver->quit();
        }
    }

    /**
     * @param string $deviceName
     * @param string $platformVersion
     * @param string $deviceOrientation
     * @param bool $fully
     * @param DesiredCapabilities $caps
     * @return Eyes
     */
    private function initEyes($deviceName, $platformVersion, $deviceOrientation, $fully, $caps)
    {
        $this->logsPath = $_SERVER["APPLITOOLS_LOGS_PATH"];
        $eyes = new Eyes();

        $eyes->setBatch(TestDataProvider::$BatchInfo);

        $caps->setCapability("appiumVersion", "1.7.2");
        $caps->setCapability("deviceName", $deviceName);
        $caps->setCapability("deviceOrientation", $deviceOrientation);
        $caps->setCapability("platformVersion", $platformVersion);
        $caps->setCapability("platformName", "iOS");

        $caps->setCapability("username", $_SERVER["SAUCE_USERNAME"]);
        $caps->setCapability("accesskey", $_SERVER["SAUCE_ACCESS_KEY"]);

        $eyes->setStitchMode(StitchMode::SCROLL);
        $eyes->addProperty("Orientation", $deviceOrientation);
        $eyes->addProperty("Stitched", $fully ? "True" : "False");
        return $eyes;
    }

    /**
     * @param Eyes $eyes
     * @param string $testName
     */
    private function initLogging($eyes, $testName)
    {
        if (!isset($_SERVER["CI"])) {
            $date = date("Y_m_d H_i_s");
            $logPath = $this->logsPath . DIRECTORY_SEPARATOR . "PHP" . DIRECTORY_SEPARATOR . "IOSTest $testName $date";
            $logFilename = $logPath . DIRECTORY_SEPARATOR . "log.log";
            $eyes->setLogHandler(new FileLogger($logFilename, false, true));
            $eyes->setSaveDebugScreenshots(true);
            $eyes->setDebugScreenshotsPath($logPath);
        } else {
            $eyes->setLogHandler(new PrintLogHandler(true));
        }
    }

}