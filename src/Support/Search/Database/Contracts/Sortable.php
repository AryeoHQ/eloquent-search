<?php

declare(strict_types=1);

namespace Support\Search\Database\Contracts;

use Support\Primitives\Direction;
use Support\Primitives\Sort;
use Support\Primitives\Text;

interface Sortable
{
    public function sort(null|string|Text|Sort $sort, null|string|Direction $direction = null): static;
}
