<?php

declare(strict_types=1);

namespace Support\Search\Scout\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class Reindex
{
    public string $on;

    public string $location;

    public function __construct(string $on, string $location)
    {
        $this->on = $on;
        $this->location = $location;
    }
}
