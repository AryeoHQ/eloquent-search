<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Scout\Agents;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Tests\Fixtures\Support\Scout\Agencies\Agency;

class Membership extends Pivot
{
    /** @return BelongsTo<Agency, $this> */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /** @return BelongsTo<Agent, $this> */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
