<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common\Basic;

/**
 * Base builder for all query objects.
 *
 * @package Aura.SqlQuery
 */
abstract class Builder
{
    /**
     * Builds the flags as a space-separated string.
     *
     * @param array<string,mixed> $flags the flags to build
     */
    public function buildFlags(array $flags): string
    {
        if ([] === $flags) {
            return ''; // not applicable
        }

        return ' ' . \implode(' ', \array_keys($flags));
    }

    /**
     * Builds the `WHERE` clause of the statement.
     *
     * @param array<string|array<string,mixed>> $where the WHERE elements
     */
    public function buildWhere(array $where): string
    {
        if (empty($where)) {
            return ''; // not applicable
        }

        return \PHP_EOL . 'WHERE' . $this->indent($where);
    }

    /**
     * Builds the `ORDER BY ...` clause of the statement.
     *
     * @param string[] $order_by the ORDER BY elements
     */
    public function buildOrderBy(array $order_by): string
    {
        if (empty($order_by)) {
            return ''; // not applicable
        }

        return \PHP_EOL . 'ORDER BY' . $this->indentCsv($order_by);
    }

    /**
     * Builds the `LIMIT` clause of the statement.
     *
     * @param int $limit the LIMIT element
     */
    public function buildLimit(int $limit): string
    {
        if (empty($limit)) {
            return '';
        }
        return \PHP_EOL . "LIMIT {$limit}";
    }

    /**
     * Builds the `LIMIT ... OFFSET` clause of the statement.
     *
     * @param int $limit  the LIMIT element
     * @param int $offset the OFFSET element
     */
    public function buildLimitOffset(int $limit, int $offset): string
    {
        $clause = '';

        if (! empty($limit)) {
            $clause .= "LIMIT {$limit}";
        }

        if (! empty($offset)) {
            $clause .= " OFFSET {$offset}";
        }

        if (! empty($clause)) {
            $clause = \PHP_EOL . \trim($clause);
        }

        return $clause;
    }

    /**
     * Returns an array as an indented comma-separated values string.
     *
     * @param string[] $list the values to convert
     */
    public function indentCsv(array $list): string
    {
        return \PHP_EOL . '    '
             . \implode(',' . \PHP_EOL . '    ', $list);
    }

    /**
     * Returns an array as an indented string.
     *
     * @param string[] $list the values to convert
     */
    public function indent(array $list): string
    {
        return \PHP_EOL . '    '
             . \implode(\PHP_EOL . '    ', $list);
    }
}
