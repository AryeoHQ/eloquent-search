<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Scout\Agencies\Events;

use Tests\Fixtures\Support\Scout\Agencies\Agency;

class Renamed
{
    public Agency $agency;

    public function __construct(Agency $agency)
    {
        $this->agency = $agency;
    }
}
