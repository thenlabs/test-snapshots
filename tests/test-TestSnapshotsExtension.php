<?php

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use ThenLabs\ClassBuilder\ClassBuilder;
use ThenLabs\TestSnapshots\Driver\AbstractDriver;
use ThenLabs\TestSnapshots\SnapshotsPerTestInterface;
use ThenLabs\TestSnapshots\TestSnapshotsExtension;

testCase(function () {
    test(function () {
        $this->assertEmpty(TestSnapshotsExtension::getSnapshot());
    });

    test(function () {
        TestSnapshotsExtension::addDriver('driver1', new class extends AbstractDriver {
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

        TestSnapshotsExtension::addDriver('driver2', new class extends AbstractDriver {
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

        $this->assertEquals($expected, TestSnapshotsExtension::getSnapshot());
    });

    test(function () {
        $driver1 = $this->getMockBuilder(AbstractDriver::class)
            ->setMethods(['reset'])
            ->getMockForAbstractClass();
        $driver1->expects($this->once())
            ->method('reset')
        ;

        $driver2 = $this->getMockBuilder(AbstractDriver::class)
            ->setMethods(['reset'])
            ->getMockForAbstractClass();
        $driver2->expects($this->once())
            ->method('reset')
        ;

        TestSnapshotsExtension::addDriver('driver1', $driver1);
        TestSnapshotsExtension::addDriver('driver2', $driver2);

        TestSnapshotsExtension::resetAll();
    });

    test(function () {
        $driver1 = $this->getMockBuilder(AbstractDriver::class)
            ->setMethods(['reset'])
            ->getMockForAbstractClass();
        $driver1->expects($this->exactly(0))
            ->method('reset')
        ;

        $driver2 = $this->getMockBuilder(AbstractDriver::class)
            ->setMethods(['reset'])
            ->getMockForAbstractClass();
        $driver2->expects($this->once())
            ->method('reset')
        ;

        TestSnapshotsExtension::addDriver('driver1', $driver1);
        TestSnapshotsExtension::addDriver('driver2', $driver2);

        TestSnapshotsExtension::reset('driver2');
    });

    testCase(function () {
        setUp(function () {
            TestSnapshotsExtension::clearSnapshots();
            TestSnapshotsExtension::clearDrivers();
            TestSnapshotsExtension::clearExpectations();

            $this->extension = new TestSnapshotsExtension();
        });

        test(function () {
            $classBuilder = (new ClassBuilder())
                ->extends(TestCase::class)
                ->implements(SnapshotsPerTestInterface::class)
                ->addMethod('test1', function () {
                })->end()
                ->install()
            ;

            $testCase = $classBuilder->newInstance();
            $testName = $classBuilder->getFCQN().'::test1';

            $this->extension->executeBeforeTest($testName);
            $testCase->test1();
            $this->extension->executeAfterTest($testName, 1.0);

            $this->assertTrue(true);
        });

        test(function () {
            $classBuilder = (new ClassBuilder())
                ->extends(TestCase::class)
                ->implements(SnapshotsPerTestInterface::class)
                ->addMethod('test1', function () {
                })->end()
                ->install()
            ;

            $testCase = $classBuilder->newInstance();
            $testName = $classBuilder->getFCQN().'::test1';

            TestSnapshotsExtension::addDriver('driver1', new class extends AbstractDriver {
                public function getData(): array
                {
                    return [
                        'key1' => 'value1',
                    ];
                }

                public function reset(): void
                {
                }
            });

            $this->extension->executeBeforeTest($testName);
            $testCase->test1();
            $this->extension->executeAfterTest($testName, 1.0);

            $this->assertTrue(true);
        });

        test(function () {
            $this->expectException(AssertionFailedError::class);

            $classBuilder = (new ClassBuilder())
                ->extends(TestCase::class)
                ->implements(SnapshotsPerTestInterface::class)
                ->addMethod('test1', function () {
                    TestSnapshotsExtension::addDriver('driver1', new class extends AbstractDriver {
                        public function getData(): array
                        {
                            return [
                                'key1' => 'value11',
                                'key3' => 'value3',
                                'key4' => 'value4',
                            ];
                        }

                        public function reset(): void
                        {
                        }
                    });
                })->end()
                ->install()
            ;

            $testCase = $classBuilder->newInstance();
            $testName = $classBuilder->getFCQN().'::test1';

            TestSnapshotsExtension::addDriver('driver1', new class extends AbstractDriver {
                public function getData(): array
                {
                    return [
                        'key1' => 'value1',
                        'key2' => 'value2',
                        'key3' => 'value3',
                    ];
                }

                public function reset(): void
                {
                }
            });

            $this->extension->executeBeforeTest($testName);
            $testCase->test1();
            $this->extension->executeAfterTest($testName, 1.0);
        });

        test(function () {
            $classBuilder = (new ClassBuilder())
                ->extends(TestCase::class)
                ->addMethod('test1', function () {
                    TestSnapshotsExtension::addDriver('driver1', new class extends AbstractDriver {
                        public function getData(): array
                        {
                            return [
                                'key1' => 'value11',
                                'key3' => 'value3',
                                'key4' => 'value4',
                            ];
                        }

                        public function reset(): void
                        {
                        }
                    });
                })->end()
                ->install()
            ;

            $testCase = $classBuilder->newInstance();
            $testName = $classBuilder->getFCQN().'::test1';

            TestSnapshotsExtension::addDriver('driver1', new class extends AbstractDriver {
                public function getData(): array
                {
                    return [
                        'key1' => 'value1',
                        'key2' => 'value2',
                        'key3' => 'value3',
                    ];
                }

                public function reset(): void
                {
                }
            });

            $this->extension->executeBeforeTest($testName);
            $testCase->test1();
            $this->extension->executeAfterTest($testName, 1.0);

            $this->assertTrue(true);
        });

        test(function () {
            $classBuilder = (new ClassBuilder())
                ->extends(TestCase::class)
                ->implements(SnapshotsPerTestInterface::class)
                ->addMethod('test1', function () use (&$classBuilder) {
                    TestSnapshotsExtension::addDriver('driver1', new class extends AbstractDriver {
                        public function getData(): array
                        {
                            return [
                                'key1' => 'value11',
                                'key3' => 'value3',
                                'key4' => 'value4',
                            ];
                        }

                        public function reset(): void
                        {
                        }
                    });

                    $testName = $classBuilder->getFCQN().'::test1';

                    TestSnapshotsExtension::expectSnapshotDiff(
                        [
                            'CREATED' => [
                                'driver1' => [
                                    'key4' => 'value4',
                                ],
                            ],
                            'UPDATED' => [
                                'driver1' => [
                                    'key1' => 'value11',
                                ],
                            ],
                            'DELETED' => [
                                'driver1' => [
                                    'key2' => 'value2',
                                ],
                            ],
                        ],
                        $testName
                    );
                })->end()
                ->install()
            ;

            $testCase = $classBuilder->newInstance();
            $testName = $classBuilder->getFCQN().'::test1';

            TestSnapshotsExtension::addDriver('driver1', new class extends AbstractDriver {
                public function getData(): array
                {
                    return [
                        'key1' => 'value1',
                        'key2' => 'value2',
                        'key3' => 'value3',
                    ];
                }

                public function reset(): void
                {
                }
            });

            $this->extension->executeBeforeTest($testName);
            $testCase->test1();
            $this->extension->executeAfterTest($testName, 1.0);

            $this->assertTrue(true);
        });

        test(function () {
            $classBuilder = (new ClassBuilder())
                ->extends(TestCase::class)
                ->implements(SnapshotsPerTestInterface::class)
                ->addMethod('test1', function () {
                    TestSnapshotsExtension::addDriver('driver1', new class extends AbstractDriver {
                        public function getData(): array
                        {
                            return [
                                'key1' => 'value11',
                                'key3' => 'value3',
                                'key4' => 'value4',
                            ];
                        }

                        public function reset(): void
                        {
                        }
                    });

                    TestSnapshotsExtension::expectSnapshotDiff(
                        [
                            'CREATED' => [
                                'driver1' => [
                                    'key4' => 'value4',
                                ],
                            ],
                            'UPDATED' => [
                                'driver1' => [
                                    'key1' => 'value11',
                                ],
                            ],
                            'DELETED' => [
                                'driver1' => [
                                    'key2' => 'value2',
                                ],
                            ],
                        ],
                    );
                })->end()
                ->install()
            ;

            $testCase = $classBuilder->newInstance();
            $testName = $classBuilder->getFCQN().'::test1';

            TestSnapshotsExtension::addDriver('driver1', new class extends AbstractDriver {
                public function getData(): array
                {
                    return [
                        'key1' => 'value1',
                        'key2' => 'value2',
                        'key3' => 'value3',
                    ];
                }

                public function reset(): void
                {
                }
            });

            $this->extension->executeBeforeTest($testName);
            $testCase->test1();
            $this->extension->executeAfterTest($testName, 1.0);

            $this->assertTrue(true);
        });
    });
});
