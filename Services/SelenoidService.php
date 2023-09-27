<?php

/**
 * Laravel service for Selenoid [A cross browser Selenium solution for Docker](https://aerokube.com)
 * Service allows a browser opening and window manipulation. It is using Docker to launch browsers.
 * App testing, website scraping, automation, ...
 * 
 * This service requires `php-webdriver/webdriver` package to be installed
 */

declare(strict_types=1);

namespace App\Services;

use Facebook\WebDriver\Remote\RemoteWebDriver;

class SelenoidService
{
    public string $sessionId;
    protected string $selenoidUrl; // http://localhost:4444/wd/hub
    protected array $defaultCapabilities;
    private RemoteWebDriver $driver;
    private array $availableSelenoids;

    public function __construct()
    {
        $this->setCapabilities();
        $this->setAvailableSelenoids();
        $this->setRandomSelenoid();
    }

    /**
     * List of selenoids here
     * 
     * http://URL_OR_IP_ADDRESS:4444/wd/hub
     * http://URL_OR_IP_ADDRESS:8080 - Selenoid GUI
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
        'version' => '81.0', // 101.0
        'browserName' => 'chrome',
        'enableVNC' => true,
        'timeZone' => 'Europe/Warsaw',
        'sessionTimeout' => '1h',
        '-session-delete-timeout' => '1h',
        '-max-timeout' => '1h',
        '-timeout' => '1h',
        '-limit' => '1h',
    ]): void
    {
        $this->defaultCapabilities = $capabilities;
    }

    public function createSession(int $connectionTimeoutMs = 1 * 60 * 60 * 1000, int $requestTimeoutMs = 1 * 60 * 60 * 1000): void
    {
        $this->driver = RemoteWebDriver::create($this->selenoidUrl, $this->defaultCapabilities, $connectionTimeoutMs, $requestTimeoutMs);
        $this->sessionId = $this->getSessionId();
    }

    public function connectToSession(string $sessionId, int $connectionTimeoutMs = 1 * 60 * 60 * 1000, int $requestTimeoutMs = 1 * 60 * 60 * 1000): void
    {
        $this->driver = RemoteWebDriver::createBySessionID($sessionId, $this->selenoidUrl, $connectionTimeoutMs, $requestTimeoutMs);
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

    public function getPageSource(string $url)
    {
        $this->driver->get($url);

        return $this->driver->getPageSource();
    }
}
