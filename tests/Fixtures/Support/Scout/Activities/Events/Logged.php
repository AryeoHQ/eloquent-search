<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Scout\Activities\Events;

use Illuminate\Database\Eloquent\Collection;
use Tests\Fixtures\Support\Scout\Activities\Activity;

class Logged
{
    /** @var Collection<int, Activity> */
    public Collection $activities;

    /** @param  Collection<int, Activity>  $activities */
    public function __construct(Collection $activities)
    {
        $this->activities = $activities;
    }
}
