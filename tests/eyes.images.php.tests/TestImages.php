<?php

namespace Tests\Applitools\Images;

use Applitools\FileLogger;
use Applitools\Images\Eyes;
use Applitools\PrintLogHandler;
use Applitools\RectangleSize;
use Applitools\Region;
use PHPUnit\Framework\TestCase;

require_once('TestDataProvider.php');

class TestImages extends TestCase
{

    public function init(Eyes $eyes, $testName)
    {
        try {

            if (!isset($_SERVER["CI"])) {
                $date = date("Y_m_d H_i_s");

                $logsPath = "";
                if (isset($_SERVER["APPLITOOLS_LOGS_PATH"])) {
                    $logsPath = $_SERVER["APPLITOOLS_LOGS_PATH"];
                }

                $logPath = $logsPath . DIRECTORY_SEPARATOR . "PHP" . DIRECTORY_SEPARATOR . "$testName $date";
                $logFilename = $logPath . DIRECTORY_SEPARATOR . "log.log";
                $eyes->setLogHandler(new FileLogger($logFilename, false, true));
                $eyes->setSaveDebugScreenshots(true);
                $eyes->setDebugScreenshotsPath($logPath);
                $eyes->setDebugScreenshotsPrefix($testName);
            } else {
                $eyes->setLogHandler(new PrintLogHandler(true));
            }

        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @throws \Applitools\Exceptions\EyesException
     * @throws \Applitools\Exceptions\NewTestException
     * @throws \Applitools\Exceptions\TestFailedException
     * @throws \Exception
     */
    public function TestCheckImage()
    {
        $eyes = new Eyes();
        $this->init($eyes, __FUNCTION__);
        $eyes->setBatch(TestDataProvider::$BatchInfo);

        try {
            // Start visual testing
            $eyes->open("Applitools Test", "Sanity Test", new RectangleSize(800, 500));

            // Load image and validate
            $img = __DIR__."/minions-800x500.jpg";
            // Visual validation point #1
            $eyes->checkImage($img, "Minions");

            // Load another image and validate
            $img = __DIR__."/minions-800x500.jpg";
            // Visual validation point #2
            $eyes->checkRegion($img, Region::CreateFromLTWH(100, 100, 200, 200), "Resources page");

            // End visual testing. Validate visual correctness.
            $eyes->close();
        } finally {
            $eyes->abortIfNotClosed();
        }
    }
}

