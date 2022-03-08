<?php
declare(strict_types=1);

namespace ThenLabs\TestSnapshots;

use Brick\VarExporter\VarExporter;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeTestHook;
use ThenLabs\SnapshotsComparator\Comparator as SnapshotsComparator;
use ThenLabs\SnapshotsComparator\ExpectationBuilder;
use ThenLabs\TestSnapshots\Driver\AbstractDriver;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Extension implements BeforeTestHook, AfterTestHook
{
    /**
     * @var array<string, AbstractDriver>
     */
    protected static $drivers = [];

    protected static $snapshotsPerTest = [];

    public function executeBeforeTest(string $testName): void
    {
        static::$snapshotsPerTest[$testName] = [
            'before' => static::getSnapshot(),
            'after' => [],
            'expectations' => new ExpectationBuilder(),
        ];
    }

    public function executeAfterTest(string $testName, float $time): void
    {
        static::$snapshotsPerTest[$testName]['after'] = static::getSnapshot();

        $snapshotsDiff = SnapshotsComparator::compare(
            static::$snapshotsPerTest[$testName]['before'],
            static::$snapshotsPerTest[$testName]['after'],
            static::$snapshotsPerTest[$testName]['expectations'],
        );

        $unexpectations = $snapshotsDiff->getUnexpectations();

        if (!empty($unexpectations)) {
            throw new AssertionFailedError(
                "\nUnexpectations:\n".VarExporter::export($unexpectations)
            );
        }
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

    public static function resetAll(): void
    {
        foreach (static::$drivers as $driver) {
            $driver->reset();
        }
    }

    public static function reset(string $driverName): void
    {
        $driver = static::$drivers[$driverName] ?? null;

        if ($driver instanceof AbstractDriver) {
            $driver->reset();
        }
    }

    public static function clearDrivers(): void
    {
        static::$drivers = [];
    }

    public static function clearSnapshots(): void
    {
        static::$snapshotsPerTest = [];
    }
}
