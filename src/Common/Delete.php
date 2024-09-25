<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

use Aura\SqlQuery\Common\Basic\DmlQuery;

/**
 * An object for DELETE queries.
 *
 * @package Aura.SqlQuery
 */
class Delete extends DmlQuery implements DeleteInterface
{
    // use WhereTrait;

    /**
     * The table to delete from.
     *
     * @var string
     */
    protected $from;

    public function __construct(
        protected QuoterInterface $quoter,
        protected DeleteBuilder $builder,
    ) {
    }

    /**
     * Sets the table to delete from.
     *
     * @param string $table the table to delete from
     *
     * @return $this
     */
    public function from(string $table): self
    {
        $this->from = $this->quoter->quoteName($table);
        return $this;
    }

    /**
     * Builds this query object into a string.
     *
     * @return string
     */
    protected function build(): string
    {
        return 'DELETE'
            . $this->builder->buildFlags($this->flags)
            . $this->builder->buildFrom($this->from)
            . $this->builder->buildWhere($this->where)
            . $this->builder->buildOrderBy($this->order_by);
    }
}
