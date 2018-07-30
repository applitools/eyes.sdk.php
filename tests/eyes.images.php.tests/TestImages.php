<?php

namespace Tests\Applitools\Images;

use Applitools\Images\Eyes;
use Applitools\RectangleSize;
use Applitools\Region;
use Applitools\Location;
use PHPUnit\Framework\TestCase;

class TestImages extends TestCase
{
    /**
     * @throws \Applitools\Exceptions\EyesException
     * @throws \Applitools\Exceptions\NewTestException
     * @throws \Applitools\Exceptions\TestFailedException
     * @throws \Exception
     */
    public function testSearch()
    {
        $eyes = new Eyes();
        $eyes->setApiKey($_SERVER['APPLITOOLS_API_KEY']);
        $eyes->setHostOS("Windows7");
        $eyes->setHostApp("My maxthon browser");

        try {
            // Start visual testing
            $eyes->open("Applitools Test", "Sanity Test", new RectangleSize(800, 500));

            // Load page image and validate
            $img = "yourpath/element-test/ElementTestPage/minions-800x500_2.jpg";
            // Visual validation point #1
            $eyes->checkImage($img, "Contact-us page");

            // Load another page and validate
            $img = "yourpath/element-test/ElementTestPage/minions-800x500.jpg";
            // Visual validation point #2
            $eyes->checkRegion($img, Region::CreateFromLTWH(100,100,200,200), "Resources page");

            $eyes->addMouseTriggerCursor("click", Region::CreateFromLTWH(0,0,50,50), new Location(150, 150));

            // End visual testing. Validate visual correctness.
            $eyes->close();
        } finally {
            $eyes->abortIfNotClosed();
        }
    }
}

