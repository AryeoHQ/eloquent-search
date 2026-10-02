<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling;

use Illuminate\Database\Eloquent\Model;
use Support\Search\Scout\Attributes\ScoutQueue;
use Support\Search\Scout\Contracts\Searchable;
use Support\Search\Scout\InteractsWithSearchEngine;

#[ScoutQueue('search')]
class ValidSearchable extends Model implements Searchable
{
    use InteractsWithSearchEngine;

    public function toSearchableArray(): array
    {
        return ['id' => $this->getKey()];
    }

    public function searchableAs(): string
    {
        return 'valid_searchables';
    }
}
