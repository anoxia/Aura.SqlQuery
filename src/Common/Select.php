<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

use Aura\SqlQuery\AbstractQuery;
use Aura\SqlQuery\Exception;

/**
 * An object for SELECT queries.
 *
 * @package Aura.SqlQuery
 */
class Select extends AbstractQuery implements SelectInterface
{
    use WhereTrait;
    use LimitOffsetTrait { limit as setLimit;
        offset as setOffset; }

    /**
     * An array of union SELECT statements.
     *
     * @var array
     */
    protected $union = [];

    /**
     * Is this a SELECT FOR UPDATE?
     *
     * @var
     */
    protected $for_update = false;

    /**
     * The columns to be selected.
     *
     * @var array
     */
    protected $cols = [];

    /**
     * Select from these tables; includes JOIN clauses.
     *
     * @var array
     */
    protected $from = [];

    /**
     * The current key in the `$from` array.
     *
     * @var int
     */
    protected $from_key = -1;

    /**
     * Tracks which JOIN clauses are attached to which FROM tables.
     *
     * @var array
     */
    protected $join = [];

    /**
     * GROUP BY these columns.
     *
     * @var array
     */
    protected $group_by = [];

    /**
     * The list of HAVING conditions.
     *
     * @var array
     */
    protected $having = [];

    /**
     * The page number to select.
     *
     * @var int
     */
    protected $page = 0;

    /**
     * The number of rows per page.
     *
     * @var int
     */
    protected $paging = 10;

    /**
     * Tracks table references to avoid duplicate identifiers.
     *
     * @var array
     */
    protected $table_refs = [];

    /**
     * Returns this query object as an SQL statement string.
     *
     * @return string an SQL statement string
     */
    public function getStatement()
    {
        $union = '';
        if (! empty($this->union)) {
            $union = \implode(\PHP_EOL, $this->union) . \PHP_EOL;
        }
        return $union . $this->build();
    }

    /**
     * Sets the number of rows per page.
     *
     * @param int $paging the number of rows to page at
     *
     * @return $this
     */
    public function setPaging($paging)
    {
        $this->paging = (int) $paging;
        if ($this->page) {
            $this->setPagingLimitOffset();
        }
        return $this;
    }

    /**
     * Gets the number of rows per page.
     *
     * @return int the number of rows per page
     */
    public function getPaging()
    {
        return $this->paging;
    }

    /**
     * Makes the select FOR UPDATE (or not).
     *
     * @param bool $enable whether or not the SELECT is FOR UPDATE (default
     *                     true)
     *
     * @return $this
     */
    public function forUpdate($enable = true)
    {
        $this->for_update = (bool) $enable;
        return $this;
    }

    /**
     * Makes the select DISTINCT (or not).
     *
     * @param bool $enable whether or not the SELECT is DISTINCT (default
     *                     true)
     *
     * @return $this
     */
    public function distinct($enable = true)
    {
        $this->setFlag('DISTINCT', $enable);
        return $this;
    }

    /**
     * Is the select DISTINCT?
     *
     * @return bool
     */
    public function isDistinct()
    {
        return $this->hasFlag('DISTINCT');
    }

    /**
     * Adds columns to the query.
     *
     * Multiple calls to cols() will append to the list of columns, not
     * overwrite the previous columns.
     *
     * @param array $cols The column(s) to add to the query. The elements can be
     *                    any mix of these: `array("col", "col AS alias", "col" => "alias")`
     *
     * @return $this
     */
    public function cols(array $cols)
    {
        foreach ($cols as $key => $val) {
            $this->addCol($key, $val);
        }
        return $this;
    }

    /**
     * Adds a column and alias to the columns to be selected.
     *
     * @param mixed $key If an integer, ignored. Otherwise, the column to be
     *                   added.
     * @param mixed $val if $key was an integer, the column to be added;
     *                   otherwise, the column alias
     */
    protected function addCol($key, $val)
    {
        if (\is_string($key)) {
            // [col => alias]
            $this->cols[$val] = $key;
        } else {
            $this->addColWithAlias($val);
        }
    }

    /**
     * Adds a column with an alias to the columns to be selected.
     *
     * @param string $spec the column specification: "col alias",
     *                     "col AS alias", or something else entirely
     */
    protected function addColWithAlias($spec)
    {
        $parts = \explode(' ', $spec);
        $count = \count($parts);
        if (2 == $count) {
            // "col alias"
            $this->cols[$parts[1]] = $parts[0];
        } elseif (3 == $count && 'AS' == \mb_strtoupper($parts[1])) {
            // "col AS alias"
            $this->cols[$parts[2]] = $parts[0];
        } else {
            // no recognized alias
            $this->cols[] = $spec;
        }
    }

    /**
     * Remove a column via its alias.
     *
     * @param string $alias The column to remove
     *
     * @return bool
     */
    public function removeCol($alias)
    {
        if (isset($this->cols[$alias])) {
            unset($this->cols[$alias]);

            return true;
        }

        $index = \array_search($alias, $this->cols);
        if (false !== $index) {
            unset($this->cols[$index]);
            return true;
        }

        return false;
    }

    /**
     * Has the column or alias been added to the query?
     *
     * @param string $alias The column or alias to look for
     *
     * @return bool
     */
    public function hasCol($alias)
    {
        return isset($this->cols[$alias]) || false !== \array_search($alias, $this->cols);
    }

    /**
     * Does the query have any columns in it?
     *
     * @return bool
     */
    public function hasCols()
    {
        return (bool) $this->cols;
    }

    /**
     * Returns a list of columns.
     *
     * @return array
     */
    public function getCols()
    {
        return $this->cols;
    }

    /**
     * Tracks table references.
     *
     * @param string $type FROM, JOIN, etc
     * @param string $spec the table and alias name
     *
     * @throws Exception when the reference has already been used
     */
    protected function addTableRef($type, $spec)
    {
        $name = $spec;

        $pos = \mb_strripos($name, ' AS ');
        if (false !== $pos) {
            $name = \trim(\mb_substr($name, $pos + 4));
        }

        if (isset($this->table_refs[$name])) {
            $used = $this->table_refs[$name];
            throw new Exception("Cannot reference '{$type} {$spec}' after '{$used}'");
        }

        $this->table_refs[$name] = "{$type} {$spec}";
    }

    /**
     * Adds a FROM element to the query; quotes the table name automatically.
     *
     * @param string $spec the table specification; "foo" or "foo AS bar"
     *
     * @return $this
     */
    public function from($spec)
    {
        $this->addTableRef('FROM', $spec);
        return $this->addFrom($this->quoter->quoteName($spec));
    }

    /**
     * Adds a raw unquoted FROM element to the query; useful for adding FROM
     * elements that are functions.
     *
     * @param string $spec The table specification, e.g. "function_name()".
     *
     * @return $this
     */
    public function fromRaw($spec)
    {
        $this->addTableRef('FROM', $spec);
        return $this->addFrom($spec);
    }

    /**
     * Adds to the $from property and increments the key count.
     *
     * @param string $spec the table specification
     *
     * @return $this
     */
    protected function addFrom($spec)
    {
        $this->from[] = [$spec];
        $this->from_key++;
        return $this;
    }

    /**
     * Adds an aliased sub-select to the query.
     *
     * @param string|Select $spec if a Select object, use as the sub-select;
     *                            if a string, the sub-select string
     * @param string        $name the alias name for the sub-select
     *
     * @return $this
     */
    public function fromSubSelect($spec, $name)
    {
        $this->addTableRef('FROM (SELECT ...) AS', $name);
        $spec = $this->subSelect($spec, '        ');
        $name = $this->quoter->quoteName($name);
        return $this->addFrom("({$spec}    ) AS {$name}");
    }

    /**
     * Formats a sub-SELECT statement, binding values from a Select object as
     * needed.
     *
     * @param string|SelectInterface $spec   a sub-SELECT specification
     * @param string                 $indent indent each line with this string
     *
     * @return string the sub-SELECT string
     */
    protected function subSelect($spec, $indent)
    {
        if ($spec instanceof SelectInterface) {
            $this->bindValues($spec->getBindValues());
        }

        return \PHP_EOL . $indent
            . \ltrim(\preg_replace('/^/m', $indent, (string) $spec))
            . \PHP_EOL;
    }

    /**
     * Adds a JOIN table and columns to the query.
     *
     * @param string $join the join type: inner, left, natural, etc
     * @param string $spec the table specification; "foo" or "foo AS bar"
     * @param string $cond join on this condition
     * @param array  $bind values to bind to ?-placeholders in the condition
     *
     * @return $this
     *
     * @throws Exception
     */
    public function join($join, $spec, $cond = null, array $bind = [])
    {
        $join = \mb_strtoupper(\ltrim("{$join} JOIN"));
        $this->addTableRef($join, $spec);

        $spec = $this->quoter->quoteName($spec);
        $cond = $this->fixJoinCondition($cond, $bind);
        return $this->addJoin(\rtrim("{$join} {$spec} {$cond}"));
    }

    /**
     * Fixes a JOIN condition to quote names in the condition and prefix it
     * with a condition type ('ON' is the default and 'USING' is recognized).
     *
     * @param string $cond join on this condition
     * @param array  $bind values to bind to ?-placeholders in the condition
     *
     * @return string
     */
    protected function fixJoinCondition($cond, array $bind)
    {
        if (! $cond) {
            return '';
        }

        $cond = $this->quoter->quoteNamesIn($cond);
        $cond = $this->rebuildCondAndBindValues($cond, $bind);

        if ('ON ' == \mb_strtoupper(\mb_substr(\ltrim($cond), 0, 3))) {
            return $cond;
        }

        if ('USING ' == \mb_strtoupper(\mb_substr(\ltrim($cond), 0, 6))) {
            return $cond;
        }

        return 'ON ' . $cond;
    }

    /**
     * Adds a INNER JOIN table and columns to the query.
     *
     * @param string $spec the table specification; "foo" or "foo AS bar"
     * @param string $cond join on this condition
     * @param array  $bind values to bind to ?-placeholders in the condition
     *
     * @return $this
     *
     * @throws Exception
     */
    public function innerJoin($spec, $cond = null, array $bind = [])
    {
        return $this->join('INNER', $spec, $cond, $bind);
    }

    /**
     * Adds a LEFT JOIN table and columns to the query.
     *
     * @param string $spec the table specification; "foo" or "foo AS bar"
     * @param string $cond join on this condition
     * @param array  $bind values to bind to ?-placeholders in the condition
     *
     * @return $this
     *
     * @throws Exception
     */
    public function leftJoin($spec, $cond = null, array $bind = [])
    {
        return $this->join('LEFT', $spec, $cond, $bind);
    }

    /**
     * Adds a JOIN to an aliased subselect and columns to the query.
     *
     * @param string        $join the join type: inner, left, natural, etc
     * @param string|Select $spec if a Select
     *                            object, use as the sub-select; if a string, the sub-select
     *                            command string
     * @param string        $name the alias name for the sub-select
     * @param string        $cond join on this condition
     * @param array         $bind values to bind to ?-placeholders in the condition
     *
     * @return $this
     *
     * @throws Exception
     */
    public function joinSubSelect($join, $spec, $name, $cond = null, array $bind = [])
    {
        $join = \mb_strtoupper(\ltrim("{$join} JOIN"));
        $this->addTableRef("{$join} (SELECT ...) AS", $name);

        $spec = $this->subSelect($spec, '            ');
        $name = $this->quoter->quoteName($name);
        $cond = $this->fixJoinCondition($cond, $bind);

        $text = \rtrim("{$join} ({$spec}        ) AS {$name} {$cond}");
        return $this->addJoin('        ' . $text);
    }

    /**
     * Adds the JOIN to the right place, given whether or not a FROM has been
     * specified yet.
     *
     * @param string $spec the JOIN clause
     *
     * @return $this
     */
    protected function addJoin($spec)
    {
        $from_key = (-1 == $this->from_key) ? 0 : $this->from_key;
        $this->join[$from_key][] = $spec;
        return $this;
    }

    /**
     * Adds grouping to the query.
     *
     * @param array $spec the column(s) to group by
     *
     * @return $this
     */
    public function groupBy(array $spec)
    {
        foreach ($spec as $col) {
            $this->group_by[] = $this->quoter->quoteNamesIn($col);
        }
        return $this;
    }

    /**
     * Adds a HAVING condition to the query by AND.
     *
     * @param string $cond the HAVING condition
     * @param array  $bind arguments to bind to placeholders
     *
     * @return $this
     */
    public function having($cond, array $bind = [])
    {
        $this->addClauseCondWithBind('having', 'AND', $cond, $bind);
        return $this;
    }

    /**
     * Adds a HAVING condition to the query by OR.
     *
     * @param string $cond the HAVING condition
     * @param array  $bind arguments to bind to placeholders
     *
     * @return $this
     *
     * @see having()
     */
    public function orHaving($cond, array $bind = [])
    {
        $this->addClauseCondWithBind('having', 'OR', $cond, $bind);
        return $this;
    }

    /**
     * Sets the limit and count by page number.
     *
     * @param int $page limit results to this page number
     *
     * @return $this
     */
    public function page($page)
    {
        $this->page = (int) $page;
        $this->setPagingLimitOffset();
        return $this;
    }

    /**
     * Updates the limit and offset values when changing pagination.
     */
    protected function setPagingLimitOffset()
    {
        $this->setLimit(0);
        $this->setOffset(0);
        if ($this->page) {
            $this->setLimit($this->paging);
            $this->setOffset($this->paging * ($this->page - 1));
        }
    }

    /**
     * Returns the page number being selected.
     *
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Takes the current select properties and retains them, then sets
     * UNION for the next set of properties.
     *
     * @return $this
     */
    public function union()
    {
        $this->union[] = $this->build() . \PHP_EOL . 'UNION';
        $this->reset();
        return $this;
    }

    /**
     * Takes the current select properties and retains them, then sets
     * UNION ALL for the next set of properties.
     *
     * @return $this
     */
    public function unionAll()
    {
        $this->union[] = $this->build() . \PHP_EOL . 'UNION ALL';
        $this->reset();
        return $this;
    }

    /**
     * Clears the current select properties; generally used after adding a
     * union.
     */
    public function reset()
    {
        $this->resetFlags();
        $this->resetCols();
        $this->resetTables();
        $this->resetWhere();
        $this->resetGroupBy();
        $this->resetHaving();
        $this->resetOrderBy();
        $this->limit(0);
        $this->offset(0);
        $this->page(0);
        $this->forUpdate(false);
    }

    /**
     * Resets the columns on the SELECT.
     *
     * @return $this
     */
    public function resetCols()
    {
        $this->cols = [];
        return $this;
    }

    /**
     * Resets the FROM and JOIN clauses on the SELECT.
     *
     * @return $this
     */
    public function resetTables()
    {
        $this->from = [];
        $this->from_key = -1;
        $this->join = [];
        $this->table_refs = [];
        return $this;
    }

    /**
     * Resets the WHERE clause on the SELECT.
     *
     * @return $this
     */
    public function resetWhere()
    {
        $this->where = [];
        return $this;
    }

    /**
     * Resets the GROUP BY clause on the SELECT.
     *
     * @return $this
     */
    public function resetGroupBy()
    {
        $this->group_by = [];
        return $this;
    }

    /**
     * Resets the HAVING clause on the SELECT.
     *
     * @return $this
     */
    public function resetHaving()
    {
        $this->having = [];
        return $this;
    }

    /**
     * Resets the ORDER BY clause on the SELECT.
     *
     * @return $this
     */
    public function resetOrderBy()
    {
        $this->order_by = [];
        return $this;
    }

    /**
     * Resets the UNION and UNION ALL clauses on the SELECT.
     *
     * @return $this
     */
    public function resetUnions()
    {
        $this->union = [];
        return $this;
    }

    /**
     * Builds this query object into a string.
     *
     * @return string
     */
    protected function build()
    {
        $cols = [];
        foreach ($this->cols as $key => $val) {
            if (\is_int($key)) {
                $cols[] = $this->quoter->quoteNamesIn($val);
            } else {
                $cols[] = $this->quoter->quoteNamesIn("{$val} AS {$key}");
            }
        }

        return 'SELECT'
            . $this->builder->buildFlags($this->flags)
            . $this->builder->buildCols($cols)
            . $this->builder->buildFrom($this->from, $this->join)
            . $this->builder->buildWhere($this->where)
            . $this->builder->buildGroupBy($this->group_by)
            . $this->builder->buildHaving($this->having)
            . $this->builder->buildOrderBy($this->order_by)
            . $this->builder->buildLimitOffset($this->limit, $this->offset)
            . $this->builder->buildForUpdate($this->for_update);
    }

    /**
     * Sets a limit count on the query.
     *
     * @param int $limit the number of rows to select
     *
     * @return $this
     */
    public function limit($limit)
    {
        $this->setLimit($limit);
        if ($this->page) {
            $this->page = 0;
            $this->setOffset(0);
        }
        return $this;
    }

    /**
     * Sets a limit offset on the query.
     *
     * @param int $offset start returning after this many rows
     *
     * @return $this
     */
    public function offset($offset)
    {
        $this->setOffset($offset);
        if ($this->page) {
            $this->page = 0;
            $this->setLimit(0);
        }
        return $this;
    }

    /**
     * Adds a column order to the query.
     *
     * @param array $spec the columns and direction to order by
     *
     * @return $this
     */
    public function orderBy(array $spec)
    {
        return $this->addOrderBy($spec);
    }
}
