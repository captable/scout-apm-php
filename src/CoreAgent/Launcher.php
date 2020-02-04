<?php

declare(strict_types=1);

namespace Scoutapm\CoreAgent;

use Psr\Log\LoggerInterface;
use Throwable;
use function array_map;
use function exec;
use function explode;
use function function_exists;
use function implode;
use function in_array;
use function ini_get;
use function sprintf;

class Launcher
{
    /** @var LoggerInterface */
    private $logger;
    /** @var string */
    private $coreAgentSocketPath;
    /** @var string|null */
    private $coreAgentLogLevel;
    /** @var string */
    private $coreAgentLogFile;
    /** @var string|null */
    private $coreAgentConfigFile;

    public function __construct(
        LoggerInterface $logger,
        string $coreAgentSocketPath,
        ?string $coreAgentLogLevel,
        ?string $coreAgentLogFile,
        ?string $coreAgentConfigFile
    ) {
        $this->logger              = $logger;
        $this->coreAgentSocketPath = $coreAgentSocketPath;
        $this->coreAgentLogLevel   = $coreAgentLogLevel;
        $this->coreAgentConfigFile = $coreAgentConfigFile;
        $this->coreAgentLogFile    = $coreAgentLogFile ?? '/dev/null';
    }

    public function launch(string $coreAgentBinaryPath) : bool
    {
        if (! $this->phpCanExec()) {
            return false;
        }

        $this->logger->debug('Core Agent Launch in Progress');
        try {
            $commandParts = [
                $coreAgentBinaryPath,
                'start',
                '--daemonize',
                'true',
                '--log-file',
                $this->coreAgentLogFile,
            ];

            if ($this->coreAgentLogLevel !== null) {
                $commandParts[] = '--log-level';
                $commandParts[] = $this->coreAgentLogLevel;
            }

            if ($this->coreAgentConfigFile !== null) {
                $commandParts[] = '--config-file';
                $commandParts[] = $this->coreAgentConfigFile;
            }

            $commandParts[] = '--socket';
            $commandParts[] = $this->coreAgentSocketPath;

            $escapedCommand = implode(' ', array_map('escapeshellarg', $commandParts));

            $this->logger->debug(sprintf('Launching core agent with command: %s', $escapedCommand));

            exec($escapedCommand);

            return true;
        } catch (Throwable $e) {
            $this->logger->debug(
                sprintf('Failed to launch core agent - exception %s', $e->getMessage()),
                ['exception' => $e]
            );

            return false;
        }
    }

    private function phpCanExec() : bool
    {
        if (! function_exists('exec')) {
            $this->logger->warning('PHP function exec is not available');

            return false;
        }

        if (in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))))) {
            $this->logger->warning('PHP function exec is in disabled_functions');

            return false;
        }

        if (exec('echo scoutapm') !== 'scoutapm') {
            $this->logger->warning('PHP function exec did not return expected value');

            return false;
        }

        $this->logger->debug('exec is available');

        return true;
    }
}
