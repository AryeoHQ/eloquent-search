<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\Database;

use Illuminate\Database\Eloquent\Builder;
use Support\Search\Database\Contracts\Filterable;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends Builder<TModel>
 */
class FilterableWithoutHasFilters extends Builder implements Filterable
{
    public function filter(array $requestParams): static
    {
        return $this;
    }
}
