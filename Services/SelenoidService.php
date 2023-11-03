<?php

/**
 * Laravel service for Selenoid [A cross browser Selenium solution for Docker](https://aerokube.com)
 * Service allows a browser opening and window manipulation. It is using Docker to launch browsers.
 * App testing, website scraping, automation, ...
 *
 * This service requires `php-webdriver/webdriver` package to be installed
 *
 *
 * http://URL_OR_IP_ADDRESS:8080 - Selenoid GUI
 *
 * http://URL_OR_IP_ADDRESS:4444/wd/hub - Selenoid for PHP
 * http://URL_OR_IP_ADDRESS:4444/status - Selenoid status and session list
 *
 *
 * Setting up:
 * Install Selenoid on your server: https://github.com/aerokube/selenoid
 * Download CM (Configuration Manager): https://github.com/aerokube/cm/releases
 * ./cm selenoid start --vnc --args "-limit 10", where "limit" is a session limit 
 */

declare(strict_types=1);

namespace App\Services;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

class SelenoidService
{
    public string $sessionId;
    protected string $selenoidUrl; // http://localhost:4444/wd/hub
    protected array $defaultCapabilities;
    protected RemoteWebDriver $driver;
    protected array $availableSelenoids;

    public function __construct()
    {
        $this->setCapabilities();
        $this->setAvailableSelenoids();
        $this->setRandomSelenoid();
    }

    public function __destruct()
    {
        $this->closeSession();
    }

    /**
     * List of selenoids here
     */
    public function getAvailableSelenoids(): array
    {
        return $this->availableSelenoids;
    }

    public function setAvailableSelenoids(array $selenoids = ['http://localhost:4444/wd/hub']): void
    {
        $this->availableSelenoids = $selenoids;
    }

    public function getRandomSelenoid(): string
    {
        $selenoids = $this->getAvailableSelenoids();
        $random = array_rand($selenoids, 1);

        return $selenoids[$random];
    }

    public function setRandomSelenoid(): void
    {
        $this->setSelenoidUrl($this->getRandomSelenoid());
    }

    /**
     * http://localhost:4444/wd/hub
     */
    public function setSelenoidUrl(string $selenoidUrl): void
    {
        $this->selenoidUrl = $selenoidUrl;
    }

    public function getSelenoidUrl(): string
    {
        return $this->selenoidUrl;
    }

    public function setCapabilities(array $capabilities = [
        'browserName' => 'chrome',
        'enableVNC' => true,
        'timeZone' => 'Europe/Warsaw',
    ]): void
    {
        $this->defaultCapabilities = $capabilities;
    }

    public function createSession(int $connectionTimeoutMs = 1 * 60 * 60 * 1000, int $requestTimeoutMs = 1 * 60 * 60 * 1000): void
    {
        $this->driver = RemoteWebDriver::create($this->selenoidUrl, $this->defaultCapabilities, $connectionTimeoutMs, $requestTimeoutMs);
        $this->sessionId = $this->getSessionId();
    }

    public function closeSession(): void
    {
        if (!empty($this->driver)) {
            $this->driver->quit();
        }
    }

    public function getSessionId(): string
    {
        return $this->driver->getSessionID();
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function getRequest(string $url)
    {
        $this->driver->get($url);
    }

    public function getPageSource(string $url)
    {
        $this->driver->get($url);

        return $this->driver->getPageSource();
    }

    public function clickElement(string $cssSelector)
    {
        $this->driver->findElement(WebDriverBy::cssSelector($cssSelector))->click();
    }

    public function executeJsScript(string $script): void
    {
        $this->driver->executeScript($script);
    }

    public function getDownloadedList(): array
    {
        $listUrl = str_replace('/wd/hub', '/download/'.$this->getSessionId(), $this->getSelenoidUrl());
        $fileList = file_get_contents($listUrl);
        if (empty($fileList)) {
            return [];
        }

        $fileList = array_filter(explode("\n", strip_tags($fileList)));
        foreach ($fileList as $k => $item) {
            $fileList[$item] = $listUrl.'/'.rawurlencode($item);
            unset($fileList[$k]);
        }

        return $fileList;
    }
}
