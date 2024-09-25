<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

/**
 * Common INSERT builder.
 *
 * @package Aura.SqlQuery
 */
class InsertBuilder extends Basic\Builder
{
    /**
     * Builds the INTO clause.
     *
     * @param string $into the INTO element
     *
     * @return string
     */
    public function buildInto($into)
    {
        return " INTO {$into}";
    }


    /**
     * Builds the inserted columns and values of the statement.
     *
     * @param array $col_values the column names and values
     *
     * @return string
     */
    public function buildValuesForInsert(array $col_values)
    {
        return ' ('
            . $this->indentCsv(\array_keys($col_values))
            . \PHP_EOL . ') VALUES ('
            . $this->indentCsv(\array_values($col_values))
            . \PHP_EOL . ')';
    }

    /**
     * Builds the bulk-inserted columns and values of the statement.
     *
     * @param array $col_order       the column names to insert, in order
     * @param array $col_values_bulk the bulk-insert values, in the same order
     *                               the column names
     *
     * @return string
     */
    public function buildValuesForBulkInsert(array $col_order, array $col_values_bulk)
    {
        $cols = '    (' . \implode(', ', $col_order) . ')';
        $vals = [];
        foreach ($col_values_bulk as $row_values) {
            $vals[] = '    (' . \implode(', ', $row_values) . ')';
        }
        return \PHP_EOL . $cols . \PHP_EOL
            . 'VALUES' . \PHP_EOL
            . \implode(',' . \PHP_EOL, $vals);
    }
}
