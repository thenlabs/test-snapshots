<?php

use ThenLabs\TestSnapshots\Extension;
use ThenLabs\TestSnapshots\AbstractDriver;

testCase(function () {
    testCase('#getSnapshot()', function () {
        test(function () {
            $this->assertEmpty(Extension::getSnapshot());
        });

        test(function () {
            Extension::addDriver('driver1', new class extends AbstractDriver {
                public function getData(): array
                {
                    return [
                        'key1' => 'val1',
                        'key2' => 'val2',
                    ];
                }

                public function reset(): void
                {
                }
            });

            Extension::addDriver('driver2', new class extends AbstractDriver {
                public function getData(): array
                {
                    return [
                        'key3' => 'val3',
                    ];
                }

                public function reset(): void
                {
                }
            });

            $expected = [
                'driver1' => [
                    'key1' => 'val1',
                    'key2' => 'val2',
                ],
                'driver2' => [
                    'key3' => 'val3',
                ],
            ];

            $this->assertEquals($expected, Extension::getSnapshot());
        });
    });
});