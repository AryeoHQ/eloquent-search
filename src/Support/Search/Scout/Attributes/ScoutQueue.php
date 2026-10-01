<?php

declare(strict_types=1);

namespace Support\Search\Scout\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ScoutQueue
{
    public function __construct(
        public string $queue,
    ) {}
}
