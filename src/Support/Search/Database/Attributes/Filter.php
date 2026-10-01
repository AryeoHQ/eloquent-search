<?php

declare(strict_types=1);

namespace Support\Search\Database\Attributes;

use Attribute;
use Support\Search\Database\Contracts;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Filter implements Contracts\Filter
{
    public function __construct(
        public string $name,
    ) {}
}
