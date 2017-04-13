eyes.php
=============
Getting started
----------------
1. Install Applitools sdk.php using [Composer](https://getcomposer.org/):

	```bash
	composer require applitools/eyes.sdk.php:dev-master phpunit/phpunit
	```
	
	This will also install PHPUnit, which is necessary to run the example test bellow.

2. Download and run selenium server.

	Server version should be suitable to your browser version.
3. Create test class.

	As example SomeTest.php:
    ```php
    <?php

    use Applitools\Eyes;
    use Applitools\RectangleSize;
    use Facebook\WebDriver\Remote\RemoteWebDriver;
    use Facebook\WebDriver\Remote\WebDriverCapabilityType;
    use Facebook\WebDriver\WebDriverBy;
    use PHPUnit\Framework\TestCase;

    class SomeTest extends TestCase
    {
        protected $url = 'http://php.net'; //Example url
        /** @var RemoteWebDriver */
        protected $webDriver;

        public function setUp()
        {
            $capabilities = array(WebDriverCapabilityType::BROWSER_NAME => 'chrome');
            $this->webDriver = RemoteWebDriver::create('http://localhost:4444/wd/hub', $capabilities);
        }

        public function tearDown()
        {
            $this->webDriver->close();
        }

        public function testSearch()
        {
            $this->webDriver->get($this->url);
            $eyes = new Eyes();
            $eyes->setApiKey('---YOUR APPLITOOLS API KEY---');
            $eyes->setHideScrollbars(true);
            $eyes->setStitchMode("CSS");
            $eyes->setForceFullPageScreenshot(true);
            $appName = 'Example_app_name';
            $testName = 'Example_test_name';
            $eyes->open($this->webDriver, $appName, $testName, new RectangleSize(1024, 500));
            $eyes->checkWindow("Example_tag_name");
            $eyes->checkFrame(WebDriverBy::xpath("//*[@src=\"frame1.html\"]"), 3, "Elem_1");
            $eyes->close();
        }
    }
    ```
4. Run the test.

	If class was created in the folder with eyes.sdk.php command should be:
	    
	```bash
	vendor/bin/phpunit SomeTest -v
	```
