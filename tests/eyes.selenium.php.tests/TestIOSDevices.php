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

    /** @var Eyes */
    protected $eyes;

    private $logsPath = ".";

    public function data()
    {
        return [
                ["iPhone X Simulator", "11.2", "portrait", false],
                ["iPhone X Simulator", "11.2", "landscape", false],
                ["iPhone 7 Simulator", "11.0", "portrait", false],
                ["iPhone 7 Simulator", "11.0", "landscape", false],
                ["iPhone 7 Simulator", "10.0", "portrait", false],
                ["iPhone 7 Simulator", "10.0", "landscape", false],
                ["iPhone 6 Plus Simulator", "11.0", "portrait", false],
                ["iPhone 6 Plus Simulator", "11.0", "landscape", false],
                ["iPhone 6 Plus Simulator", "10.0", "portrait", false],
                ["iPhone 6 Plus Simulator", "10.0", "landscape", false],
                ["iPhone 5s Simulator", "10.0", "portrait", false],
                ["iPhone 5s Simulator", "10.0", "landscape", false],
                ["iPad Simulator", "11.0", "portrait", false],
                ["iPad Simulator", "11.0", "landscape", false],
                ["iPad Pro (9.7 inch) Simulator", "11.0", "portrait", false],
                ["iPad Pro (9.7 inch) Simulator", "11.0", "landscape", false],
                ["iPad Pro (12.9 inch) Simulator", "11.0", "portrait", false],
                ["iPad Pro (12.9 inch) Simulator", "11.0", "landscape", false],
                ["iPad Pro (12.9 inch) (2nd generation) Simulator", "11.0", "portrait", false],
                ["iPad Pro (12.9 inch) (2nd generation) Simulator", "11.0", "landscape", false],
                ["iPad Pro (10.5 inch) Simulator", "11.0", "portrait", false],
                ["iPad Pro (10.5 inch) Simulator", "11.0", "landscape", false],
                ["iPad (5th generation) Simulator", "11.0", "portrait", false],
                ["iPad (5th generation) Simulator", "11.0", "landscape", false],
                ["iPad Air Simulator", "11.0", "portrait", false],
                ["iPad Air Simulator", "11.0", "landscape", false],
                ["iPad Air 2 Simulator", "11.0", "portrait", false],
                ["iPad Air 2 Simulator", "11.0", "landscape", false],
                ["iPhone X Simulator", "11.2", "portrait", true],
                ["iPhone X Simulator", "11.2", "landscape", true],
                ["iPhone 7 Simulator", "11.0", "portrait", true],
                ["iPhone 7 Simulator", "11.0", "landscape", true],
                ["iPhone 7 Simulator", "10.0", "portrait", true],
                ["iPhone 7 Simulator", "10.0", "landscape", true],
                ["iPhone 6 Plus Simulator", "11.0", "portrait", true],
                ["iPhone 6 Plus Simulator", "11.0", "landscape", true],
                ["iPhone 6 Plus Simulator", "10.0", "portrait", true],
                ["iPhone 6 Plus Simulator", "10.0", "landscape", true],
                ["iPhone 5s Simulator", "10.0", "portrait", true],
                ["iPhone 5s Simulator", "10.0", "landscape", true],
                ["iPad Simulator", "11.0", "portrait", true],
                ["iPad Simulator", "11.0", "landscape", true],
                ["iPad Pro (9.7 inch) Simulator", "11.0", "portrait", true],
                ["iPad Pro (9.7 inch) Simulator", "11.0", "landscape", true],
                ["iPad Pro (12.9 inch) Simulator", "11.0", "portrait", true],
                ["iPad Pro (12.9 inch) Simulator", "11.0", "landscape", true],
                ["iPad Pro (12.9 inch) (2nd generation) Simulator", "11.0", "portrait", true],
                ["iPad Pro (12.9 inch) (2nd generation) Simulator", "11.0", "landscape", true],
                ["iPad Pro (10.5 inch) Simulator", "11.0", "portrait", true],
                ["iPad Pro (10.5 inch) Simulator", "11.0", "landscape", true],
                ["iPad (5th generation) Simulator", "11.0", "portrait", true],
                ["iPad (5th generation) Simulator", "11.0", "landscape", true],
                ["iPad Air Simulator", "11.0", "portrait", true],
                ["iPad Air Simulator", "11.0", "landscape", true],
                ["iPad Air 2 Simulator", "11.0", "portrait", true],
                ["iPad Air 2 Simulator", "11.0", "landscape", true]
        ];
    }

    /**
     * @test
     * @dataProvider data
     * @doesNotPerformAssertions
     * @param string $deviceName
     * @param string $deviceOrientation
     * @param string $platformVersion
     * @param boolean $fully
     */
    public function TestIOSSafariCrop($deviceName, $platformVersion, $deviceOrientation, $fully)
    {
        $this->logsPath = $_SERVER["APPLITOOLS_LOGS_PATH"];
        $this->eyes = new Eyes();

        $this->eyes->setBatch(TestDataProvider::$BatchInfo);

        $caps = new DesiredCapabilities();

        $caps->setCapability("appiumVersion", "1.7.2");
        $caps->setCapability("deviceName", $deviceName);
        $caps->setCapability("deviceOrientation", $deviceOrientation);
        $caps->setCapability("platformVersion", $platformVersion);
        $caps->setCapability("platformName", "iOS");
        $caps->setCapability("browserName", "Safari");

        $caps->setCapability("username", $_SERVER["SAUCE_USERNAME"]);
        $caps->setCapability("accesskey", $_SERVER["SAUCE_ACCESS_KEY"]);

        $testName = "$deviceName $platformVersion $deviceOrientation";
        if ($fully) {
            $testName .= " fully";
        }

        $caps->setCapability("name", "$testName ({$this->eyes->getFullAgentId()})");

        $sauceUrl = "http://ondemand.saucelabs.com/wd/hub";
        $driver = RemoteWebDriver::create($sauceUrl, $caps, null, 240000);

        if (!isset($_SERVER["CI"])) {
            $date = date("Y_m_d H_i_s");
            $logPath = $this->logsPath . DIRECTORY_SEPARATOR . "PHP" . DIRECTORY_SEPARATOR . "IOSTest $testName $date";
            $logFilename = $logPath . DIRECTORY_SEPARATOR . "log.log";
            $this->eyes->setLogHandler(new FileLogger($logFilename, false, true));
            $this->eyes->setSaveDebugScreenshots(true);
            $this->eyes->setDebugScreenshotsPath($logPath);
        } else {
            $this->eyes->setLogHandler(new PrintLogHandler(true));
        }

        $this->eyes->setStitchMode(StitchMode::SCROLL);

        $this->eyes->addProperty("Orientation", $deviceOrientation);
        $this->eyes->addProperty("Stitched", $fully ? "True" : "False");

        try {
            $driver->get("https://www.applitools.com/customers");
            $this->eyes->open($driver, "Eyes Selenium SDK - iOS Safari Cropping", $testName);
            $this->eyes->check("Initial view", Target::region(WebDriverBy::cssSelector(".horizontal-page"))->fully($fully));
            $this->eyes->close();
        } finally {
            $this->eyes->abortIfNotClosed();
            $driver->quit();
        }
    }
}