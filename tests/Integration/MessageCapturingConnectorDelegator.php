<?php

declare(strict_types=1);

namespace Scoutapm\IntegrationTests;

use Scoutapm\Connector\Command;
use Scoutapm\Connector\Connector;

use function json_decode;
use function json_encode;

final class MessageCapturingConnectorDelegator implements Connector
{
    /** @var array<array-key, mixed> */
    public $sentMessages = [];

    /** @var Connector */
    private $delegate;

    public function __construct(Connector $delegate)
    {
        $this->delegate = $delegate;
    }

    public function connect(): void
    {
        $this->delegate->connect();
    }

    public function connected(): bool
    {
        return $this->delegate->connected();
    }

    public function sendCommand(Command $message): string
    {
        $this->sentMessages[] = json_decode(json_encode($message), true);

        return $this->delegate->sendCommand($message);
    }

    public function shutdown(): void
    {
        $this->delegate->shutdown();
    }
}
