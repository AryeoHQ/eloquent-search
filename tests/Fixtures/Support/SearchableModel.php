<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;
use Support\Search\Scout\Attributes\ScoutConnection;
use Support\Search\Scout\Attributes\ScoutQueue;
use Support\Search\Scout\Attributes\UseScoutBuilder;
use Support\Search\Scout\Contracts\Searchable;
use Support\Search\Scout\InteractsWithSearchEngine;

#[ScoutQueue('test-queue')]
#[ScoutConnection('test-connection')]
#[UseScoutBuilder(Builder::class)]
class SearchableModel extends Model implements Searchable
{
    use InteractsWithSearchEngine;

    public function toSearchableArray(): array
    {
        return ['id' => $this->id];
    }

    public function searchableAs(): string
    {
        return 'searchable_models';
    }
}
