<?php
/**
 * Applitools software
 */

namespace Tests\Applitools\Selenium;

use Applitools\Exceptions\NewTestException;
use Applitools\Exceptions\TestFailedException;
use Applitools\RectangleSize;
use Applitools\Selenium\Eyes;
use Applitools\Selenium\fluent\Target;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use PHPUnit\Framework\TestCase;

class TestServerStatus extends TestCase
{
    /** @var Eyes */
    private $eyes;

    /** @var WebDriver */
    private $webDriver;


    public function setUp()
    {
        $this->eyes = new Eyes();
        $this->eyes->setApiKey($_SERVER['APPLITOOLS_API_KEY']);
        $this->eyes->setSaveNewTests(false);

        /** @var ChromeOptions */
        $options = new ChromeOptions();
        $options->addArguments(["disable-infobars"]);

        /** @var DesiredCapabilities $caps */
        $caps = DesiredCapabilities::chrome();
        $caps->setCapability(ChromeOptions::CAPABILITY, $options);

        $this->webDriver = RemoteWebDriver::create("http://localhost:4444/wd/hub", $caps);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @expectedException \Applitools\Exceptions\TestFailedException
     */
    public function TestSessionSummary_Status_Failed()
    {
        $driver = $this->eyes->open($this->webDriver, "TestServerStatus", "TestServerStatus", new RectangleSize(800, 599));

        $driver->get("http://applitools.github.io/demo/TestPages/FramesTestPage/");
        $this->eyes->check("TestSessionSummary_Status_Failed", Target::window());
        $this->localTearDown();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @expectedException \Applitools\Exceptions\DiffsFoundException
     */
    public function TestSessionSummary_Status_Diff()
    {
        $driver = $this->eyes->open($this->webDriver, "TestServerStatus", "TestServerStatus", new RectangleSize(800, 599));

        $driver->get("http://applitools.github.io/demo/TestPages/FramesTestPage/");
        $this->eyes->check("TestSessionSummary_Status_Diff", Target::window());
        $this->localTearDown();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @expectedException \Applitools\Exceptions\NewTestException
     */
    public function TestSessionSummary_Status_New()
    {
        $guid = '_' . bin2hex(openssl_random_pseudo_bytes(16));
        $driver = $this->eyes->open($this->webDriver, "TestServerStatus" . $guid, "TestServerStatus" . $guid, new RectangleSize(800, 599));

        $driver->get("http://applitools.github.io/demo/TestPages/FramesTestPage/");
        $this->eyes->check("TestSessionSummary_Status_New" . $guid, Target::window());
        $this->localTearDown();
    }

    private function localTearDown()
    {
        try {
            $this->eyes->close();
        } finally {
            $this->eyes->abortIfNotClosed();
            $this->webDriver->quit();
        }
    }
}
