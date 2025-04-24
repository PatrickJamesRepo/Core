<?php

namespace Tests;

use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        // Tell Dusk to use our Selenium container URL
        $seleniumUrl = env('SELENIUM_URL', 'http://selenium:4444/wd/hub');

        // Chrome options
        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless',
            '--window-size=1920,1080',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--ignore-certificate-errors',
        ]);

        return RemoteWebDriver::create(
            $seleniumUrl,
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
