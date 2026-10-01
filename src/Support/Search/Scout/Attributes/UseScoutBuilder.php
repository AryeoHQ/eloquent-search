<?php

declare(strict_types=1);

namespace Support\Search\Scout\Attributes;

use Attribute;
use Laravel\Scout\Builder;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class UseScoutBuilder
{
    /**
     * @param  class-string<Builder>  $builder
     */
    public function __construct(
        public string $builder,
    ) {}
}
