<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Scout\Agents\Events;

use Tests\Fixtures\Support\Scout\Agents\Agent;

class Saved
{
    public Agent $agent;

    public function __construct(Agent $agent)
    {
        $this->agent = $agent;
    }
}
