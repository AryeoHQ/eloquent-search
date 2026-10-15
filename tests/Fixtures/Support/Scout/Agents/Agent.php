<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Scout\Agents;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tests\Fixtures\Support\Scout\Agencies\Agency;
use Tests\Fixtures\Support\Scout\Teams\Team;

#[UseFactory(Factory::class)]
class Agent extends Model
{
    /** @use HasFactory<Factory> */
    use HasFactory;

    protected $dispatchesEvents = [
        'saved' => Events\Saved::class,
    ];

    /** @return BelongsTo<Agency, $this> */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'primary_agency_id');
    }

    /** @return BelongsTo<Team, $this> */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /** @return BelongsToMany<Agency, $this, Membership> */
    public function agencies(): BelongsToMany
    {
        return $this->belongsToMany(Agency::class)->using(Membership::class);
    }
}
