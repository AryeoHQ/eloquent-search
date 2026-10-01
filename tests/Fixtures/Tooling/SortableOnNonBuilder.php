<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling;

use Support\Primitives\Direction;
use Support\Primitives\Sort;
use Support\Primitives\Text;
use Support\Search\Database\Contracts\Sortable;

class SortableOnNonBuilder implements Sortable
{
    public function sort(null|string|Text|Sort $sort, null|string|Direction $direction = null): static
    {
        return $this;
    }
}
