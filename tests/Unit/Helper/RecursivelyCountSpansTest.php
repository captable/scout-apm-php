<?php

declare(strict_types=1);

namespace Scoutapm\UnitTests\Helper;

use Exception;
use PHPUnit\Framework\TestCase;
use Scoutapm\Connector\Command;
use Scoutapm\Events\Request\Request;
use Scoutapm\Events\Request\RequestId;
use Scoutapm\Events\Span\Span;
use Scoutapm\Events\Span\SpanId;
use Scoutapm\Events\Tag\TagRequest;
use Scoutapm\Events\Tag\TagSpan;
use Scoutapm\Helper\RecursivelyCountSpans;

use function uniqid;

/** @covers \Scoutapm\Helper\RecursivelyCountSpans */
final class RecursivelyCountSpansTest extends TestCase
{
    /**
     * @return Command[][][]|int[][]
     *
     * @throws Exception
     *
     * @psalm-return array<int, array{commands: array<int, Command>, expectedSpans: int}>
     */
    public function commandAndExpectedSpansCountProvider(): array
    {
        $spanWithChildren = new Span(new Request(), uniqid('span', true), RequestId::new());
        $spanWithChildren->appendChild(new Span(new Request(), uniqid('span', true), RequestId::new()));
        $spanWithChildren->tag(uniqid('tag', true), uniqid('value', true));

        return [
            [
                'commands' => [
                    new Span(new Request(), uniqid('span', true), RequestId::new()),
                ],
                'expectedSpans' => 1,
            ],
            [
                'commands' => [],
                'expectedSpans' => 0,
            ],
            [
                'commands' => [
                    new TagSpan(uniqid('tag', true), uniqid('value', true), RequestId::new(), SpanId::new()),
                    new TagRequest(uniqid('tag', true), uniqid('value', true), RequestId::new()),
                    new Span(new Request(), uniqid('span', true), RequestId::new()),
                ],
                'expectedSpans' => 1,
            ],
            [
                'commands' => [
                    new Span(new Request(), uniqid('span', true), RequestId::new()),
                    new Span(new Request(), uniqid('span', true), RequestId::new()),
                ],
                'expectedSpans' => 2,
            ],
            [
                'commands' => [$spanWithChildren],
                'expectedSpans' => 2,
            ],
        ];
    }

    /**
     * @param Command[] $commands
     *
     * @dataProvider commandAndExpectedSpansCountProvider
     */
    public function testForCommands(array $commands, int $expectedSpans): void
    {
        self::assertSame($expectedSpans, RecursivelyCountSpans::forCommands($commands));
    }
}
