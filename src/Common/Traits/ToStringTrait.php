<?php

declare(strict_types=1);

namespace Aura\SqlQuery\Common\Traits;

trait ToStringTrait
{
    public function __toString(): string
    {
        return $this->build();
    }
}
