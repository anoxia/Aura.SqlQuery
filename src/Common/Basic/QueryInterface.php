<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common\Basic;

/**
 * Interface for query objects.
 *
 * @package Aura.SqlQuery
 */
interface QueryInterface extends WhereInterface
{
    /**
     * Returns this query object as an SQL statement string.
     */
    public function getStatement(): string;

    /**
     * Returns the prefix to use when quoting identifier names.
     */
    public function getQuoteNamePrefix(): string;

    /**
     * Returns the suffix to use when quoting identifier names.
     */
    public function getQuoteNameSuffix(): string;

    /**
     * Reset all query flags.
     */
    public function resetFlags(): self;
}
