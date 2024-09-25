<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

use Aura\SqlQuery\AuraSqlQueryException;
use Aura\SqlQuery\Common\Basic\Columns;
use Aura\SqlQuery\Common\Basic\QuoterInterface;

/**
 * An object for INSERT queries.
 *
 * @package Aura.SqlQuery
 */
class Insert extends Columns implements InsertInterface, \Stringable
{
    use Traits\QuoteNameTrait;
    use Traits\ToStringTrait;

    /**
     * The table to insert into (quoted).
     */
    protected string $into;

    /**
     * The table to insert into (raw, for last-insert-id use).
     */
    protected string $into_raw;

    /**
     * A map of fully-qualified `table.column` names to last-insert-id names.
     * This is used to look up the right last-insert-id name for a given table
     * and column. Generally useful only for extended tables in Postgres.
     *
     * @var array<string,mixed>
     */
    protected array $last_insert_id_names;

    /**
     * The current row-number we are adding column values for. This comes into
     * play only with bulk inserts.
     */
    protected int $row = 0;

    /**
     * A collection of `$col_values` for previous rows in bulk inserts.
     */
    protected array $col_values_bulk = [];

    /**
     * A collection of `$bind_values` for previous rows in bulk inserts.
     */
    protected array $bind_values_bulk = [];

    /**
     * The order in which columns will be bulk-inserted; this is taken from the
     * very first inserted row.
     *
     * @var array
     */
    protected $col_order = [];

    public function __construct(
        protected QuoterInterface $quoter,
        protected InsertBuilder $builder,
    ) {
    }

    /**
     * Sets the map of fully-qualified `table.column` names to last-insert-id
     * names. Generally useful only for extended tables in Postgres.
     *
     * @param array $last_insert_id_names the list of ID names
     */
    public function setLastInsertIdNames(array $last_insert_id_names): void
    {
        $this->last_insert_id_names = $last_insert_id_names;
    }

    /**
     * Sets the table to insert into.
     *
     * @param string $into the table to insert into
     */
    public function into(string $into): self
    {
        $this->into_raw = $into;
        $this->into = $this->quoter->quoteName($into);
        return $this;
    }

    /**
     * Builds this query object into a string.
     */
    protected function build(): string
    {
        $stm = 'INSERT'
            . $this->builder->buildFlags($this->flags)
            . $this->builder->buildInto($this->into);

        if ($this->row) {
            $this->finishRow();
            $stm .= $this->builder->buildValuesForBulkInsert($this->col_order, $this->col_values_bulk);
        } else {
            $stm .= $this->builder->buildValuesForInsert($this->col_values);
        }

        return $stm;
    }

    /**
     * Returns the proper name for passing to `PDO::lastInsertId()`.
     *
     * @param string $col the last insert ID column
     *
     * @return mixed normally null, since most drivers do not need a name;
     *               alternatively, a string from `$last_insert_id_names`
     */
    public function getLastInsertIdName($col): mixed
    {
        $key = $this->into_raw . '.' . $col;
        return $this->last_insert_id_names[$key] ?? null;
    }

    /**
     * Gets the values to bind to placeholders.
     *
     * @return array
     */
    public function getBindValues(): array
    {
        return \array_merge(parent::getBindValues(), $this->bind_values_bulk);
    }

    /**
     * Adds multiple rows for bulk insert.
     *
     * @param array $rows An array of rows, where each element is an array of
     *                    column key-value pairs. The values are bound to placeholders.
     *
     * @return $this
     */
    public function addRows(array $rows): self
    {
        foreach ($rows as $cols) {
            $this->addRow($cols);
        }
        if ($this->row > 1) {
            $this->finishRow();
        }
        return $this;
    }

    /**
     * Add one row for bulk insert; increments the row counter and optionally
     * adds columns to the new row.
     *
     * When adding the first row, the counter is not incremented.
     *
     * After calling `addRow()`, you can further call `col()`, `cols()`, and
     * `set()` to work with the newly-added row. Calling `addRow()` again will
     * finish off the current row and start a new one.
     *
     * @param array $cols an array of column key-value pairs; the values are
     *                    bound to placeholders
     *
     * @return $this
     */
    public function addRow(array $cols = []): self
    {
        if ([] === $this->col_values) {
            return $this->cols($cols);
        }

        if ([] === $this->col_order) {
            $this->col_order = \array_keys($this->col_values);
        }

        $this->finishRow();
        $this->row++;
        $this->cols($cols);
        return $this;
    }

    /**
     * Finishes off the current row in a bulk insert, collecting the bulk
     * values and resetting for the next row.
     */
    protected function finishRow(): void
    {
        if ([] === $this->col_values) {
            return;
        }

        foreach ($this->col_order as $col) {
            $this->finishCol($col);
        }

        $this->col_values = [];
        $this->bind_values = [];
    }

    /**
     * Finishes off a single column of the current row in a bulk insert.
     *
     * @param string $col the column to finish off
     *
     * @throws Exception on named column missing from row
     */
    protected function finishCol($col): void
    {
        if (! \array_key_exists($col, $this->col_values)) {
            throw new AuraSqlQueryException("Column {$col} missing from row {$this->row}.");
        }

        // get the current col_value
        $value = $this->col_values[$col];

        // is it *not* a placeholder?
        if (':' != \mb_substr($value, 0, 1)) {
            // copy the value as-is
            $this->col_values_bulk[$this->row][$col] = $value;
            return;
        }

        // retain col_values in bulk with the row number appended
        $this->col_values_bulk[$this->row][$col] = "{$value}_{$this->row}";

        // the existing placeholder name without : or row number
        $name = \mb_substr($value, 1);

        // retain bind_value in bulk with new placeholder
        if (\array_key_exists($name, $this->bind_values)) {
            $this->bind_values_bulk["{$name}_{$this->row}"] = $this->bind_values[$name];
        }
    }
}
