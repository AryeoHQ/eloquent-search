<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling;

use Support\Search\Database\Contracts\Filterable;

class FilterableOnNonBuilder implements Filterable
{
    public function filter(array $requestParams): static
    {
        return $this;
    }
}
