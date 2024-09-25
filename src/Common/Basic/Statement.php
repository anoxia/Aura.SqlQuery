<?php

declare(strict_types=1);

namespace Aura\SqlQuery\Common\Basic;

abstract class Statement implements StatementInterface
{
    /**
     * The list of flags.
     *
     * @var array<string,bool>
     */
    protected array $flags = [];

    /**
     * Sets or unsets specified flag.
     *
     * @param string $flag   Flag to set or unset
     * @param bool   $enable Flag status - enabled or not (default true)
     */
    protected function setFlag(string $flag, bool $enable = true): void
    {
        if ($enable) {
            $this->flags[$flag] = true;
        } else {
            unset($this->flags[$flag]);
        }
    }

    /**
     * Returns true if the specified flag was enabled by setFlag().
     *
     * @param string $flag Flag to check
     */
    protected function hasFlag(string $flag): bool
    {
        return isset($this->flags[$flag]);
    }

    /**
     * Reset all query flags.
     */
    public function resetFlags(): self
    {
        $this->flags = [];
        return $this;
    }

    /**
     * Returns this query object as an SQL statement string.
     */
    public function getStatement(): string
    {
        return $this->build();
    }
}
