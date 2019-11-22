<?php

declare(strict_types=1);

namespace Scoutapm\Config\Source;

interface ConfigSource
{
    /**
     * Returns true if this config source knows for certain it has an answer for this key
     */
    public function hasKey(string $key) : bool;

    /**
     * Returns the value for this configuration key.
     *
     * Only valid if the Source has previously returned "true" to `hasKey`
     *
     * @return mixed
     */
    public function get(string $key);
}
