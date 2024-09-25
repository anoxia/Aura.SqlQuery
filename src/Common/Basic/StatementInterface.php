<?php

declare(strict_types=1);

namespace Aura\SqlQuery\Common\Basic;

interface StatementInterface
{
    /**
     * Returns this query object as an SQL statement string.
     * Alias of build.
     */
    public function getStatement(): string;

    /**
     * Returns this query object as an SQL statement string.
     */
    public function build(): string;
}
