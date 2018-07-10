<?php
/**
 * Applitools software
 */

namespace Tests\Applitools\Selenium;

use Applitools\BatchInfo;

class TestDataProvider
{
    /** BatchInfo */
    public static $BatchInfo;
}

TestDataProvider::$BatchInfo = new BatchInfo("PHP Tests");