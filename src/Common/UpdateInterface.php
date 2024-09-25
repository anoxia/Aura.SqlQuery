<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

use Aura\SqlQuery\Common\Basic\ValuesInterface;
use Aura\SqlQuery\Common\Basic\WhereInterface;

/**
 * An interface for UPDATE queries.
 *
 * @package Aura.SqlQuery
 */
interface UpdateInterface extends ValuesInterface, WhereInterface, LimitInterface
{
    /**
     * Sets the table to update.
     *
     * @param string $table The table to update.
     */
    public function table(string $table): self;
}
