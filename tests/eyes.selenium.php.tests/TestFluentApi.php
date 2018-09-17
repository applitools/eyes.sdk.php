<?php

namespace Tests\Applitools\Selenium;

require_once('TestSetup.php');

use Applitools\Region;
use Applitools\Selenium\fluent\Target;
use Facebook\WebDriver\WebDriverBy;

abstract class TestFluentApi extends TestSetup
{
    /**
     * @test
     * @doesNotPerformAssertions
     * @throws \Exception
     */
    public function TestCheckWindowWithIgnoreRegion_Fluent()
    {
        $this->init(__FUNCTION__);
        $this->webDriver->findElement(WebDriverBy::tagName("input"))->sendKeys("My Input");
        $this->eyes->check("Fluent - Window with Ignore region",
            Target::window()
                ->fully()
                ->timeout(5000)
                ->ignore(Region::CreateFromLTWH(50, 50, 100, 100))
                ->layoutRegions(WebDriverBy::cssSelector("#overflowing-div-image"))
        );
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @throws \Exception
     */
    public function TestCheckRegionWithIgnoreRegion_Fluent()
    {
        $this->init(__FUNCTION__);
        $this->eyes->check("Fluent - Region with Ignore region",
            Target::regionBySelector(WebDriverBy::id("overflowing-div"))
                ->ignore(Region::CreateFromLTWH(50, 50, 100, 100))
        );
    }


    /**
     * @test
     * @doesNotPerformAssertions
     * @throws \Exception
     */
    public function TestCheckFrame_Fully_Fluent()
    {
        $this->init(__FUNCTION__);
        $this->eyes->check("Fluent - Full Frame", Target::frameByNameOrId("frame1")->fully());
    }


    /**
     * @test
     * @doesNotPerformAssertions
     * @throws \Exception
     */
    public function TestCheckFrame_Fluent()
    {
        $this->init(__FUNCTION__);
        $this->eyes->check("Fluent - Frame", Target::frameByNameOrId("frame1"));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @throws \Exception
     */
    public function TestCheckFrameInFrame_Fully_Fluent()
    {
        $this->init(__FUNCTION__);
        $this->eyes->check("Fluent - Full Frame in Frame", Target::frameByNameOrId("frame1")
            ->frameByNameOrId("frame1-1")
            ->fully());
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @throws \Exception
     */
    public function TestCheckRegionInFrame_Fluent()
    {
        $this->init(__FUNCTION__);
        $this->eyes->check("Fluent - Region in Frame", Target::frameByNameOrId("frame1")
            ->regionBySelector(WebDriverBy::id("inner-frame-div"))
            ->fully());
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @throws \Exception
     */
    public function TestCheckRegionInFrameInFrame_Fluent()
    {
        $this->init(__FUNCTION__);
        $this->eyes->check("Fluent - Region in Frame in Frame", Target::frameByNameOrId("frame1")
            ->frameByNameOrId("frame1-1")
            ->regionBySelector(WebDriverBy::tagName("img"))
            ->fully());
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @throws \Exception
     */
    public function TestCheckRegionInFrame2_Fluent()
    {
        $this->init(__FUNCTION__);

        $this->eyes->check("Fluent - Inner frame div 1", Target::frame("frame1")
            ->region(WebDriverBy::id("inner-frame-div"))
            ->fully()
            ->timeout(5000)
            ->ignore(Region::CreateFromLTWH(50, 50, 100, 100)));

        $this->eyes->check("Fluent - Inner frame div 2", Target::frame("frame1")
            ->region(WebDriverBy:: id("inner-frame-div"))
            ->fully()
            ->ignore(Region::CreateFromLTWH(50, 50, 100, 100))
            ->ignore(Region::CreateFromLTWH(70, 170, 90, 90)));

        $this->eyes->check("Fluent - Inner frame div 3", Target::frame("frame1")
            ->region(WebDriverBy::id("inner-frame-div"))
            ->fully()
            ->timeout(5000));

        $this->eyes->check("Fluent - Inner frame div 4", Target::frame("frame1")
            ->region(WebDriverBy::id("inner-frame-div"))
            ->fully());

        $this->eyes->check("Fluent - Full frame with floating region", Target::frame("frame1")
            ->fully()
            ->layout()
            ->floating(25, Region::CreateFromLTWH(200, 200, 150, 150)));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @throws \Exception
     */
    public function TestCheckFrameInFrame_Fully_Fluent2()
    {
        $this->init(__FUNCTION__);

        $this->eyes->check("Fluent - Window with Ignore region 2", Target::window()
            ->fully()
        );

        $this->eyes->check("Fluent - Full Frame in Frame 2", Target::frame("frame1")
            ->frame("frame1-1")
            ->fully());
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @throws \Exception
     */
    public function TestCheckWindowWithIgnoreBySelector_Fluent()
    {
        $this->init(__FUNCTION__);
        $this->eyes->check("Fluent - Window with ignore region by selector", Target::window()
            ->ignore(WebDriverBy::id("overflowing-div")));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @throws \Exception
     */
    public function TestCheckWindowWithFloatingBySelector_Fluent()
    {
        $this->init(__FUNCTION__);
        $this->eyes->check("Fluent - Window with floating region by selector", Target::window()
            ->addFloatingRegionBySelector(WebDriverBy::id("overflowing-div"), 3, 3, 20, 30));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @throws \Exception
     */
    public function TestCheckElementFully_Fluent()
    {
        $this->init(__FUNCTION__);
        $element = $this->webDriver->findElement(WebDriverBy::id("overflowing-div-image"));
        $this->eyes->check("Fluent - Region by element - fully", Target::region($element)->fully());
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @throws \Exception
     */
    public function TestCheckElement_Fluent()
    {
        $this->init(__FUNCTION__);
        $element = $this->webDriver->findElement(WebDriverBy::id("overflowing-div-image"));
        $this->eyes->check("Fluent - Region by element", Target::region($element));
    }

}
