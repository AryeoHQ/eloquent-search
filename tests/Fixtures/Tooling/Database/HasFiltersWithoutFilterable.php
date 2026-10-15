<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\Database;

use Illuminate\Database\Eloquent\Builder;
use Support\Search\Database\Provides\HasFilters;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends Builder<TModel>
 */
class HasFiltersWithoutFilterable extends Builder
{
    use HasFilters;
}
