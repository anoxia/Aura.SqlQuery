<?php

declare(strict_types=1);

namespace Aura\SqlQuery\Common\Basic;

interface ColumnsInterface
{
    /**
     * Does the query have any columns in it?
     */
    public function hasCols(): bool;

    /**
     * Sets one column value placeholder; if an optional second parameter is
     * passed, that value is bound to the placeholder.
     *
     * @param string $col   the column name
     * @param mixed  $value
     */
    public function col(string $col, ...$value): self;

    /**
     * Sets multiple column value placeholders. If an element is a key-value
     * pair, the key is treated as the column name and the value is bound to
     * that column.
     *
     * @param string[] $cols a list of column names, optionally as key-value
     *                       pairs where the key is a column name and the value is a bind value for
     *                       that column
     */
    public function cols(array $cols): self;

    /**
     * Sets a column value directly; the value will not be escaped, although
     * fully-qualified identifiers in the value will be quoted.
     *
     * @param string $col   the column name
     * @param string $value the column value expression
     */
    public function set(string $col, ?string $value): self;
}
