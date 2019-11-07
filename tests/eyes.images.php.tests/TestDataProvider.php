<?php

namespace Tests\Applitools\Images;

use Applitools\BatchInfo;

class TestDataProvider
{
    /** @var BatchInfo */
    public static $BatchInfo;
}

TestDataProvider::$BatchInfo = new BatchInfo("PHP Tests");
if (isSet($_SERVER['APPLITOOLS_BATCH_ID'])) {
    TestDataProvider::$BatchInfo->setId($_SERVER['APPLITOOLS_BATCH_ID']);
}
