<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class ScoutTraitDirectly extends Model
{
    use Searchable;

    public function toSearchableArray(): array
    {
        return ['id' => $this->getKey()];
    }
}
