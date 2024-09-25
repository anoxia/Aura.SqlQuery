<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common\Basic;

use Aura\SqlQuery\Common\SelectInterface;

/**
 * Abstract query object.
 *
 * @package Aura.SqlQuery
 */
abstract class Query extends Columns implements QueryInterface
{
    /**
     * The list of WHERE conditions.
     *
     * @var array
     */
    protected array $where = [];

    /**
     * ORDER BY these columns.
     *
     * @var array
     */
    protected array $order_by = [];

    /**
     * Constructor.
     *
     * @param QuoterInterface  $quoter  a helper for quoting identifier names
     * @param BuilderInterface $builder a builder for the query
     */
    public function __construct(
        protected QuoterInterface $quoter,
    ) {
    }

    /**
     * Builds this query object into a string.
     */
    abstract protected function build(): string;

    /**
     * Adds conditions and binds values to a clause.
     *
     * @param string              $clause the clause to work with, typically 'where' or 'having'
     * @param string              $andor  add the condition using this operator, typically 'AND' or 'OR'
     * @param callable|string     $cond   the WHERE condition
     * @param array<string,mixed> $bind   arguments to bind to placeholders
     */
    protected function addClauseCondWithBind(string $clause, string $andor, callable|string $cond, array $bind): void
    {
        if ($cond instanceof \Closure) {
            $this->addClauseCondClosure($clause, $andor, $cond);
            $this->bindValues($bind);
            return;
        }

        $cond = $this->quoter->quoteNamesIn($cond);
        $cond = $this->rebuildCondAndBindValues($cond, $bind);

        $clause = &$this->{$clause};
        if ($clause) {
            $clause[] = "{$andor} {$cond}";
        } else {
            $clause[] = $cond;
        }
    }

    /**
     * Adds to a clause through a closure, enclosing within parentheses.
     *
     * @param string   $clause  the clause to work with, typically 'where' or 'having'
     * @param string   $andor   add the condition using this operator, typically 'AND' or 'OR'
     * @param callable $closure the closure that adds to the clause
     */
    protected function addClauseCondClosure(string $clause, string $andor, callable $closure): void
    {
        // retain the prior set of conditions, and temporarily reset the clause
        // for the closure to work with (otherwise there will be an extraneous
        // opening AND/OR keyword)
        $set = $this->{$clause};
        $this->{$clause} = [];

        // invoke the closure, which will re-populate the $this->$clause
        $closure($this);

        // are there new clause elements?
        if ([] === $this->{$clause}) {
            // no: restore the old ones, and done
            $this->{$clause} = $set;
            return;
        }

        // append an opening parenthesis to the prior set of conditions,
        // with AND/OR as needed ...
        if ($set) {
            $set[] = "{$andor} (";
        } else {
            $set[] = '(';
        }

        // append the new conditions to the set, with indenting
        foreach ($this->{$clause} as $cond) {
            $set[] = "    {$cond}";
        }
        $set[] = ')';

        // ... then put the full set of conditions back into $this->$clause
        $this->{$clause} = $set;
    }

    /**
     * Rebuilds a condition string, replacing sequential placeholders with
     * named placeholders, and binding the sequential values to the named
     * placeholders.
     *
     * return the rebuilt condition string
     *
     * @param string $cond        the condition with sequential placeholders
     * @param array  $bind_values the values to bind to the sequential
     *                            placeholders under their named versions
     */
    protected function rebuildCondAndBindValues(string $cond, array $bind_values): string
    {
        $selects = [];

        foreach ($bind_values as $key => $val) {
            if ($val instanceof SelectInterface) {
                $selects[":{$key}"] = $val;
            } else {
                $this->bindValue($key, $val);
            }
        }

        foreach ($selects as $key => $select) {
            $selects[$key] = $select->getStatement();
            $this->bind_values = \array_merge(
                $this->bind_values,
                $select->getBindValues(),
            );
        }

        return \strtr($cond, $selects);
    }

    public function where(callable|string $cond, array $bind = []): self
    {
        $this->addClauseCondWithBind('where', 'AND', $cond, $bind);
        return $this;
    }

    public function orWhere(callable|string $cond, array $bind = []): self
    {
        $this->addClauseCondWithBind('where', 'OR', $cond, $bind);
        return $this;
    }

    /**
     * Adds a column order to the query.
     *
     * @param string[] $spec the columns and direction to order by
     */
    protected function addOrderBy(array $spec): self
    {
        foreach ($spec as $col) {
            $this->order_by[] = $this->quoter->quoteNamesIn($col);
        }
        return $this;
    }
}
