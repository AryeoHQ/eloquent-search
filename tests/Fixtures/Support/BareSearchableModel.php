<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support;

use Illuminate\Database\Eloquent\Model;
use Support\Search\Scout\Contracts\Searchable;
use Support\Search\Scout\InteractsWithSearchEngine;

class BareSearchableModel extends Model implements Searchable
{
    use InteractsWithSearchEngine;

    public function toSearchableArray(): array
    {
        return ['id' => $this->id];
    }

    public function searchableAs(): string
    {
        return 'bare_searchable_models';
    }
}
