<?php
declare(strict_types=1);

namespace ThenLabs\TestSnapshots;

use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeTestHook;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Extension implements BeforeTestHook, AfterTestHook
{
    /**
     * @var array<string, AbstractDriver>
     */
    protected static $drivers = [];

    public function executeBeforeTest(string $testName): void
    {
    }

    public function executeAfterTest(string $testName, float $time): void
    {
    }

    public function getTestInfo(string $testName): array
    {
        [$class, $method] = explode('::', $testName);

        return compact('class', 'method');
    }

    public static function getSnapshot(): array
    {
        $result = [];

        foreach (static::$drivers as $name => $driver) {
            $result[$name] = $driver->getData();
        }

        return $result;
    }

    public static function addDriver(string $name, AbstractDriver $driver): void
    {
        static::$drivers[$name] = $driver;
    }
}