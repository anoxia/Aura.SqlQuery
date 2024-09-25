<?php

declare(strict_types=1);

namespace Aura\SqlQuery\Common\Basic;

abstract class Columns extends Values implements ColumnsInterface
{
    /**
     * Column values for INSERT or UPDATE queries; the key is the column name and the
     * value is the column value.
     *
     * @var array<string,mixed>
     */
    protected array $col_values = [];

    /**
     * Does the query have any columns in it?
     */
    public function hasCols(): bool
    {
        return ! empty($this->col_values);
    }

    /**
     * Sets one column value placeholder; if an optional second parameter is
     * passed, that value is bound to the placeholder.
     *
     * @param string $col   the column name
     * @param array  $value Value of the column
     */
    protected function addCol(string $col, ...$value): self
    {
        $key = $this->quoter->quoteName($col);
        $this->col_values[$key] = ":{$col}";
        if (\count($value) > 0) {
            $this->bindValue($col, $value[0]);
        }
        return $this;
    }

    /**
     * Sets multiple column value placeholders. If an element is a key-value
     * pair, the key is treated as the column name and the value is bound to
     * that column.
     *
     * @param array<string,mixed> $cols a list of column names, optionally as key-value
     *                                  pairs where the key is a column name and the value is a bind value for
     *                                  that column
     */
    protected function addCols(array $cols): self
    {
        foreach ($cols as $key => $val) {
            if (\is_int($key)) {
                // integer key means the value is the column name
                $this->addCol($val);
            } else {
                // the key is the column name and the value is a value to
                // be bound to that column
                $this->addCol($key, $val);
            }
        }
        return $this;
    }

    /**
     * Sets a column value directly; the value will not be escaped, although
     * fully-qualified identifiers in the value will be quoted.
     *
     * @param string $col   the column name
     * @param string $value the column value expression
     */
    protected function setCol(string $col, ?string $value): self
    {
        if (null === $value) {
            $value = 'NULL';
        }

        $key = $this->quoter->quoteName($col);
        $value = $this->quoter->quoteNamesIn($value);
        $this->col_values[$key] = $value;
        return $this;
    }

    /**
     * Sets one column value placeholder; if an optional second parameter is
     * passed, that value is bound to the placeholder.
     *
     * @param string $col   the column name
     * @param array  $value optional: a value to bind to the placeholder
     *
     * @return $this
     */
    public function col(string $col, ...$value): self
    {
        return $this->addCol($col, ...$value);
    }

    /**
     * Sets multiple column value placeholders. If an element is a key-value
     * pair, the key is treated as the column name and the value is bound to
     * that column.
     *
     * @param array $cols a list of column names, optionally as key-value
     *                    pairs where the key is a column name and the value is a bind value for
     *                    that column
     *
     * @return $this
     */
    public function cols(array $cols): self
    {
        return $this->addCols($cols);
    }

    /**
     * Sets a column value directly; the value will not be escaped, although
     * fully-qualified identifiers in the value will be quoted.
     *
     * @param string $col   the column name
     * @param string $value the column value expression
     */
    public function set(string $col, ?string $value): self
    {
        return $this->setCol($col, $value);
    }
}
