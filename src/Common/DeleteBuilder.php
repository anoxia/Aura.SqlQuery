<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

/**
 * Common DELETE builder.
 *
 * @package Aura.SqlQuery
 */
class DeleteBuilder extends AbstractBuilder
{
    /**
     * Builds the FROM clause.
     *
     * @param string $from the FROM element
     *
     * @return string
     */
    public function buildFrom($from)
    {
        return " FROM {$from}";
    }
}
