<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Scout\Agencies;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Support\Search\Scout\Attributes\Reindex;
use Support\Search\Scout\Attributes\ScoutConnection;
use Support\Search\Scout\Attributes\ScoutQueue;
use Support\Search\Scout\Attributes\UseScoutBuilder;
use Support\Search\Scout\Contracts\Searchable;
use Support\Search\Scout\Provides\InteractsWithSearchEngine;
use Tests\Fixtures\Support\Scout\Activities\Activity;
use Tests\Fixtures\Support\Scout\Activities\Events\Logged;
use Tests\Fixtures\Support\Scout\Agencies\Events\Renamed;
use Tests\Fixtures\Support\Scout\Agents\Agent;
use Tests\Fixtures\Support\Scout\Agents\Events\Saved;
use Tests\Fixtures\Support\Scout\Agents\Membership;
use Tests\Fixtures\Support\Scout\Teams\Team;

/** @property bool $active */
#[ScoutConnection('database')]
#[ScoutQueue('search')]
#[UseFactory(Factory::class)]
#[UseScoutBuilder(Builders\Scout::class)]
class Agency extends Model implements Searchable
{
    /** @use HasFactory<Factory> */
    use HasFactory;

    use InteractsWithSearchEngine;
    use SoftDeletes;

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $with = ['agents'];

    /** @return BelongsToMany<Agent, $this, Membership> */
    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class, 'agency_agent', 'agency_id', 'agent_id')->using(Membership::class);
    }

    /** @return array<string, mixed> */
    #[Reindex(on: Renamed::class, location: 'agency')]
    #[Reindex(on: Logged::class, location: 'activities.subject.agency')]
    #[Reindex(on: 'eloquent.saved: '.Activity::class, location: 'subject')]
    #[Reindex(on: Saved::class, location: 'agent.agencies')]
    #[Reindex(on: 'eloquent.saved: '.Team::class, location: 'agents.agencies')]
    #[Reindex(on: 'eloquent.created: '.Membership::class, location: 'agency')]
    #[Reindex(on: 'eloquent.deleted: '.Membership::class, location: 'agency')]
    public function toSearchableArray(): array
    {
        return $this->toArray();
    }

    public function shouldBeSearchable(): bool
    {
        return $this->active;
    }
}
