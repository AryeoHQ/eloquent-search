<?php

declare(strict_types=1);

namespace Support\Search\Database\Contracts;

interface Filterable
{
    /**
     * @param  array<string, mixed>  $requestParams
     */
    public function filter(array $requestParams): static;
}
