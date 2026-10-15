<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Scout\Teams;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Support\Search\Scout\Contracts\Searchable;
use Support\Search\Scout\Provides\InteractsWithSearchEngine;
use Tests\Fixtures\Support\Scout\Agencies\Agency;
use Tests\Fixtures\Support\Scout\Agents\Agent;

#[UseFactory(Factory::class)]
class Team extends Model implements Searchable
{
    /** @use HasFactory<Factory> */
    use HasFactory;

    use InteractsWithSearchEngine;

    /** @return BelongsTo<Agency, $this> */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /** @return HasMany<Agent, $this> */
    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }
}
