<?php

declare(strict_types=1);

namespace Support\Search\Scout\Contracts;

interface Searchable
{
    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array;

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string;
}
