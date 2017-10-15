<?php

namespace Tests\Applitools\Selenium;

use Applitools\Selenium\UserAgent;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class UserAgentTest extends TestCase
{

    /** @test */
    public function test_Android_6_Chrome_60_UserAgent()
    {
        $uaString = "Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Mobile Safari/537.36";
        $ua = UserAgent::ParseUserAgentString($uaString, true);

        Assert::assertEquals("Android", $ua->getOS());
        Assert::assertEquals("6", $ua->getOSMajorVersion());
        Assert::assertEquals("0", $ua->getOSMinorVersion());
        Assert::assertEquals("Chrome", $ua->getBrowser());
        Assert::assertEquals("60", $ua->getBrowserMajorVersion());
        Assert::assertEquals("0", $ua->getBrowserMinorVersion());
    }

    /** @test */
    public function test_Windows_10_Chrome_60_UserAgent()
    {
        $uaString = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36";
        $ua = UserAgent::ParseUserAgentString($uaString, true);

        Assert::assertEquals("Windows", $ua->getOS());
        Assert::assertEquals("10", $ua->getOSMajorVersion());
        Assert::assertEquals("0", $ua->getOSMinorVersion());
        Assert::assertEquals("Chrome", $ua->getBrowser());
        Assert::assertEquals("60", $ua->getBrowserMajorVersion());
        Assert::assertEquals("0", $ua->getBrowserMinorVersion());
    }


    /** @test */
    public function test_IPhone_10_3_Safari_602_1_UserAgent()
    {
        $uaString = "Mozilla/5.0 (iPhone; CPU iPhone OS 10_3 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) CriOS/56.0.2924.75 Mobile/14E5239e Safari/602.1";
        $ua = UserAgent::ParseUserAgentString($uaString, true);

        Assert::assertEquals("IOS", $ua->getOS());
        Assert::assertEquals("10", $ua->getOSMajorVersion());
        Assert::assertEquals("3", $ua->getOSMinorVersion());
        Assert::assertEquals("Safari", $ua->getBrowser());
        Assert::assertEquals("602", $ua->getBrowserMajorVersion());
        Assert::assertEquals("1", $ua->getBrowserMinorVersion());
    }

    /** @test */
    public function test_Windows_10_Firefox_54_UserAgent()
    {
        $uaString = "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0";
        $ua = UserAgent::ParseUserAgentString($uaString, true);

        Assert::assertEquals("Windows", $ua->getOS());
        Assert::assertEquals("10", $ua->getOSMajorVersion());
        Assert::assertEquals("0", $ua->getOSMinorVersion());
        Assert::assertEquals("Firefox", $ua->getBrowser());
        Assert::assertEquals("54", $ua->getBrowserMajorVersion());
        Assert::assertEquals("0", $ua->getBrowserMinorVersion());
    }

}
