<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

use Aura\SqlQuery\Common\Basic\DmlQuery;
use Aura\SqlQuery\Common\Basic\QuoterInterface;

/**
 * An object for UPDATE queries.
 *
 * @package Aura.SqlQuery
 */
class Update extends DmlQuery implements UpdateInterface
{
    /**
     * The table to update.
     *
     * @var string
     */
    protected string $table;

    public function __construct(
        protected QuoterInterface $quoter,
        protected UpdateBuilder $builder,
    ) {
    }

    /**
     * Sets the table to update.
     *
     * @param string $table the table to update
     *
     * @return $this
     */
    public function table(string $table): self
    {
        $this->table = $this->quoter->quoteName($table);
        return $this;
    }

    /**
     * Builds this query object into a string.
     *
     * @return string
     */
    protected function build(): string
    {
        return 'UPDATE'
            . $this->builder->buildFlags($this->flags)
            . $this->builder->buildTable($this->table)
            . $this->builder->buildValuesForUpdate($this->col_values)
            . $this->builder->buildWhere($this->where)
            . $this->builder->buildOrderBy($this->order_by);
    }
}
