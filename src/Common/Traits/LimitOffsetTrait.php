<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

/**
 * An interface for LIMIT...OFFSET clauses.
 *
 * @package Aura.SqlQuery
 */
trait LimitOffsetTrait
{
    use LimitTrait;

    /**
     * The OFFSET value.
     *
     * @var int
     */
    protected $offset = 0;

    /**
     * Sets a limit offset on the query.
     *
     * @param int $offset start returning after this many rows
     *
     * @return $this
     */
    public function offset($offset): self
    {
        $this->offset = (int) $offset;
        return $this;
    }

    /**
     * Returns the OFFSET value.
     */
    public function getOffset(): int
    {
        return $this->offset;
    }
}
