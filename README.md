eyes.php
=============
Getting started
----------------
1. Install Applitools sdk.php:

	If you have installed phpunit, webdriver, selnium use:
	
	```bash
	composer require applitools/eyes.sdk.php:dev-master
	```
	Also use previous if you want to get the latest update.
	   
	   
	If you don't have any installed packages create composer.json file:
	   
	```json
	{
		"require": {
			"phpunit/phpunit": "5.0.*",
			"facebook/webdriver": "dev-master",
			"phpunit/phpunit-selenium": "^3.0",
			"applitools/eyes.sdk.php": "dev-master"
		}
	}
	```
	   
	And then run:
	   
	```bash
	composer install
	```

2. Download and run selenium server.

	Server version should be suitable to your browser version.
3. Create test class.

	As example SomeTest.php:
	```php
	<?php
	class SomeTest extends PHPUnit_Framework_TestCase
	{
		protected $url = 'http://php.net'; //Example url
		protected $webDriver;
		   
		public function setUp()
		{
			$capabilities = array(\WebDriverCapabilityType::BROWSER_NAME => 'chrome');
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
			$eyes->checkRegionInFrameBySelector(WebDriverBy::xpath("//*[@src=\"frame1.html\"]"), WebDriverBy::id("inner-frame-div"), 3, "Elem_1", true);
			$eyes->close();
		}
	}
	```
4. Run the test.

	If class was created in the folder with eyes.sdk.php command should be:
	    
	```bash
	vendor/bin/phpunit SomeTest -v
	```
