<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\Scout;

use Illuminate\Database\Eloquent\Model;
use Support\Search\Scout\Attributes\Reindex;
use Support\Search\Scout\Contracts\Searchable;
use Support\Search\Scout\Provides\InteractsWithSearchEngine;

abstract class ReindexOnOtherMethod extends Model implements Searchable
{
    use InteractsWithSearchEngine;

    /** @return array<string, mixed> */
    #[Reindex(on: 'event', location: 'location')]
    public function toArray(): array
    {
        return [];
    }
}
