<?php

namespace Tests\Applitools\Images;

use Applitools\Images\Eyes;
use Applitools\RectangleSize;
use Applitools\Region;
use PHPUnit\Framework\TestCase;

require_once('TestDataProvider.php');

class TestImages extends TestCase
{
    /**
     * @test
     * @throws \Applitools\Exceptions\EyesException
     * @throws \Applitools\Exceptions\NewTestException
     * @throws \Applitools\Exceptions\TestFailedException
     * @throws \Exception
     */
    public function TestCheckImage()
    {
        $eyes = new Eyes();
        $eyes->setBatch(TestDataProvider::$BatchInfo);

        try {
            // Start visual testing
            $eyes->open("Applitools Test", "Sanity Test", new RectangleSize(800, 500));

            // Load image and validate
            $img = "./minions-800x500.jpg";
            // Visual validation point #1
            $eyes->checkImage($img, "Minions");

            // Load another image and validate
            $img = "./minions-800x500.jpg";
            // Visual validation point #2
            $eyes->checkRegion($img, Region::CreateFromLTWH(100, 100, 200, 200), "Resources page");

            // End visual testing. Validate visual correctness.
            $eyes->close();
        } finally {
            $eyes->abortIfNotClosed();
        }
    }
}

