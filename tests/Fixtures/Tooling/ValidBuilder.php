<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling;

use Illuminate\Database\Eloquent\Builder;
use Support\Search\Database\Attributes\Filter;
use Support\Search\Database\Contracts\Filterable;
use Support\Search\Database\Contracts\Sortable;
use Support\Search\Database\HasFilters;
use Support\Search\Database\HasSort;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends Builder<TModel>
 */
class ValidBuilder extends Builder implements Filterable, Sortable
{
    use HasFilters;
    use HasSort;

    #[Filter('tag')]
    public function tags(string $tag): static
    {
        return $this->where('tag', $tag);
    }
}
