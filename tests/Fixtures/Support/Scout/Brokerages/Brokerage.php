<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Scout\Brokerages;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Tests\Fixtures\Support\Scout\Agencies\Agency;

#[UseFactory(Factory::class)]
class Brokerage extends Agency
{
    protected $table = 'agencies';
}
