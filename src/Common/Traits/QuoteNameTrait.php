<?php

declare(strict_types=1);

namespace Aura\SqlQuery\Common\Traits;

trait QuoteNameTrait
{
    /**
     * Returns the prefix to use when quoting identifier names.
     */
    public function getQuoteNamePrefix(): string
    {
        return $this->quoter->getQuoteNamePrefix();
    }

    /**
     * Returns the suffix to use when quoting identifier names.
     */
    public function getQuoteNameSuffix(): string
    {
        return $this->quoter->getQuoteNameSuffix();
    }

}
