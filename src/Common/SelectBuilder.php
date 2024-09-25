<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

use Aura\SqlQuery\Exception;

/**
 * Common SELECT builder.
 *
 * @package Aura.SqlQuery
 */
class SelectBuilder extends AbstractBuilder
{
    /**
     * Builds the columns portion of the SELECT.
     *
     * @param array $cols the columns
     *
     * @return string
     *
     * @throws Exception when there are no columns in the SELECT
     */
    public function buildCols(array $cols)
    {
        if (empty($cols)) {
            throw new Exception('No columns in the SELECT.');
        }
        return $this->indentCsv($cols);
    }

    /**
     * Builds the FROM clause.
     *
     * @param array $from the FROM elements
     * @param array $join the JOIN elements
     *
     * @return string
     */
    public function buildFrom(array $from, array $join)
    {
        if (empty($from)) {
            return ''; // not applicable
        }

        $refs = [];
        foreach ($from as $from_key => $from_val) {
            if (isset($join[$from_key])) {
                $from_val = \array_merge($from_val, $join[$from_key]);
            }
            $refs[] = \implode(\PHP_EOL, $from_val);
        }
        return \PHP_EOL . 'FROM' . $this->indentCsv($refs);
    }

    /**
     * Builds the GROUP BY clause.
     *
     * @param array $group_by the GROUP BY elements
     *
     * @return string
     */
    public function buildGroupBy(array $group_by)
    {
        if (empty($group_by)) {
            return ''; // not applicable
        }

        return \PHP_EOL . 'GROUP BY' . $this->indentCsv($group_by);
    }

    /**
     * Builds the HAVING clause.
     *
     * @param array $having the HAVING elements
     *
     * @return string
     */
    public function buildHaving(array $having)
    {
        if (empty($having)) {
            return ''; // not applicable
        }

        return \PHP_EOL . 'HAVING' . $this->indent($having);
    }

    /**
     * Builds the FOR UPDATE portion of the SELECT.
     *
     * @param bool $for_update true if FOR UPDATE, false if not
     *
     * @return string
     */
    public function buildForUpdate($for_update)
    {
        if (! $for_update) {
            return ''; // not applicable
        }

        return \PHP_EOL . 'FOR UPDATE';
    }
}
