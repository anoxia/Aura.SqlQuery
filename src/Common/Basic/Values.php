<?php

declare(strict_types=1);

namespace Aura\SqlQuery\Common\Basic;

abstract class Values extends Statement implements ValuesInterface
{
    /**
     * Data to be bound to the query.
     *
     * @var array<string,mixed>
     */
    protected array $bind_values = [];

    /**
     * Binds multiple values to placeholders; merges with existing values.
     *
     * @param array<string,mixed> $bind_values values to bind to placeholders
     */
    public function bindValues(array $bind_values): self
    {
        // array_merge() renumbers integer keys, which is bad for
        // question-mark placeholders
        foreach ($bind_values as $key => $val) {
            $this->bindValue($key, $val);
        }
        return $this;
    }

    /**
     * Binds a single value to the query.
     *
     * @param string $name  the placeholder name or number
     * @param mixed  $value the value to bind to the placeholder
     */
    public function bindValue(string $name, mixed $value): self
    {
        $this->bind_values[$name] = $value;
        return $this;
    }

    /**
     * Gets the values to bind to placeholders.
     *
     * @return array<string,mixed>
     */
    public function getBindValues(): array
    {
        return $this->bind_values;
    }

    /**
     * Reset all values bound to named placeholders.
     */
    public function resetBindValues(): self
    {
        $this->bind_values = [];
        return $this;
    }
}
