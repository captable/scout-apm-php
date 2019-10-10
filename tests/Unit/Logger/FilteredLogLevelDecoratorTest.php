<?php

declare(strict_types=1);

namespace Scoutapm\UnitTests\Logger;

use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Scoutapm\Logger\FilteredLogLevelDecorator;
use function str_repeat;
use function uniqid;

/** @covers \Scoutapm\Logger\FilteredLogLevelDecorator */
final class FilteredLogLevelDecoratorTest extends TestCase
{
    /** @var LoggerInterface&MockObject */
    private $decoratedLogger;

    public function setUp() : void
    {
        parent::setUp();

        $this->decoratedLogger = $this->createMock(LoggerInterface::class);
    }

    public function testLogMessagesBelowThresholdAreNotLogged() : void
    {
        $decorator = new FilteredLogLevelDecorator($this->decoratedLogger, LogLevel::NOTICE);

        $this->decoratedLogger
            ->expects(self::never())
            ->method('log');

        $decorator->info(uniqid('logMessage', true));
    }

    public function testLogMessagesAboveThresholdAreLogged() : void
    {
        $decorator = new FilteredLogLevelDecorator($this->decoratedLogger, LogLevel::NOTICE);

        $logMessage = uniqid('logMessage', true);
        $context    = [uniqid('foo', true) => uniqid('bar', true)];

        $this->decoratedLogger
            ->expects(self::once())
            ->method('log')
            ->with(LogLevel::WARNING, $logMessage, $context);

        $decorator->warning($logMessage, $context);
    }

    /** @return array<int, array<int, string>> */
    public function invalidLogLevelProvider() : array
    {
        return [
            ['lizard'],
            [''],
            [uniqid('randomString', true)],
            [str_repeat('a', 1024)],
        ];
    }

    /** @dataProvider invalidLogLevelProvider */
    public function testInvalidLogLevelsAreRejected(string $invalidLogLevel) : void
    {
        $this->expectException(InvalidArgumentException::class);
        new FilteredLogLevelDecorator($this->decoratedLogger, $invalidLogLevel);
    }
}