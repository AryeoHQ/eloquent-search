<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling;

use Illuminate\Database\Eloquent\Model;
use Support\Search\Scout\Contracts\Searchable;

class SearchableWithoutInteracts extends Model implements Searchable
{
    public function toSearchableArray(): array
    {
        return ['id' => $this->getKey()];
    }

    public function searchableAs(): string
    {
        return 'searchable_without_interacts';
    }
}
