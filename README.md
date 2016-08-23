# eyes.php
=============
Getting started
----------------
1. Install Applitools sdk.php:
   ```bash
   composer create-project --stability=dev applitools/eyes.sdk.php
   ```
2. Download and run selenium server
  Server version should be suitable to your browser version
3. Create test class.
  As example SomeClass.php
  ```php
  <?php
class SomeTest extends PHPUnit_Framework_TestCase
{
    protected $url = 'http://codeception.com'; //Example url
    protected $webDriver;

    public function setUp()
    {
        $capabilities = array(\WebDriverCapabilityType::BROWSER_NAME => 'firefox');
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
        $appName = 'Example_app_name';
        $testName = 'Example_test_name';
        $eyes->open($this->webDriver, $appName, $testName, new RectangleSize(1024, 768));
        $eyes->checkWindow("Example_tag_name");
        $eyes->close();
    }
}
  ```
4. Run the test
    If class was created in the folder with eyes.sdk.php command should be:
  ```bash
  eyes.sdk.php/vendor/bin/phpunit SomeTest -v
   ```
