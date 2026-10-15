<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\Scout;

use Illuminate\Database\Eloquent\Model;
use Support\Search\Scout\Attributes\Reindex;

class ReindexOnNonSearchable extends Model
{
    /** @return array<string, mixed> */
    #[Reindex(on: 'event', location: 'location')]
    public function toSearchableArray(): array
    {
        return [];
    }
}
