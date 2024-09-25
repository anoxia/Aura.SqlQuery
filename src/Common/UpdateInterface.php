<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

use Aura\SqlQuery\QueryInterface;

/**
 * An interface for UPDATE queries.
 *
 * @package Aura.SqlQuery
 */
interface UpdateInterface extends QueryInterface, WhereInterface, ValuesInterface
{
    /**
     * Sets the table to update.
     *
     * @param string $table the table to update
     *
     * @return $this
     */
    public function table($table);
}
