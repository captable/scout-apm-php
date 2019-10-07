<?php

declare(strict_types=1);

/**
 * Default Values Config source
 *
 * Internal to Config - see Scoutapm\Config for the public API.
 */

namespace Scoutapm\Config\Source;

use Scoutapm\Config\ConfigKey;
use function array_key_exists;

/** @internal */
class DefaultSource
{
    /** @var array<string, (string|bool|array<int, string>|int)> */
    private $defaults;

    public function __construct()
    {
        $this->defaults = $this->getDefaultConfig();
    }

    /**
     * Returns true iff this config source knows for certain it has an answer for this key
     */
    public function hasKey(string $key) : bool
    {
        return array_key_exists($key, $this->defaults);
    }

    /**
     * Returns the value for this configuration key.
     *
     * Only valid if the Source has previously returned "true" to `hasKey`
     *
     * @return string|bool|array<int, string>|int|null
     */
    public function get(string $key)
    {
        return $this->defaults[$key] ?? null;
    }

    /**
     * Returns the value for this configuration key.
     *
     * Only valid if the Source has previously returned "true" to `hasKey`
     *
     * @return array<string, (string|bool|array<int, string>|int)>
     */
    private function getDefaultConfig() : array
    {
        return [
            ConfigKey::API_VERSION => '1.0',
            ConfigKey::CORE_AGENT_DIRECTORY => '/tmp/scout_apm_core',
            ConfigKey::CORE_AGENT_DOWNLOAD_ENABLED => true,
            ConfigKey::CORE_AGENT_LAUNCH_ENABLED => true,
            ConfigKey::CORE_AGENT_VERSION => 'v1.2.2',
            ConfigKey::CORE_AGENT_DOWNLOAD_URL => 'https://s3-us-west-1.amazonaws.com/scout-public-downloads/apm_core_agent/release',
            ConfigKey::CORE_AGENT_PERMISSIONS => 0777,
            ConfigKey::MONITORING_ENABLED => false,
            ConfigKey::IGNORED_ENDPOINTS => [],
            ConfigKey::LOG_LEVEL => 'debug',
        ];
    }
}
