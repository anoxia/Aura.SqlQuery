<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

use Aura\SqlQuery\AbstractBuilder;

/**
 * Common UPDATE builder.
 *
 * @package Aura.SqlQuery
 */
class UpdateBuilder extends AbstractBuilder
{
    /**
     * Builds the table portion of the UPDATE.
     *
     * @param string $table the table name
     *
     * @return string
     */
    public function buildTable($table)
    {
        return " {$table}";
    }

    /**
     * Builds the columns and values for the statement.
     *
     * @param array $col_values the columns and values
     *
     * @return string
     */
    public function buildValuesForUpdate(array $col_values)
    {
        $values = [];
        foreach ($col_values as $col => $value) {
            $values[] = "{$col} = {$value}";
        }
        return \PHP_EOL . 'SET' . $this->indentCsv($values);
    }
}
