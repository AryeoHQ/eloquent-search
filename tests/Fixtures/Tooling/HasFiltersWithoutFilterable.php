<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling;

use Illuminate\Database\Eloquent\Builder;
use Support\Search\Database\Attributes\Filter;
use Support\Search\Database\HasFilters;
use Tests\Fixtures\Support\Role;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends Builder<TModel>
 */
class HasFiltersWithoutFilterable extends Builder
{
    use HasFilters;

    #[Filter('role')]
    public function role(string|Role $role): static
    {
        return $this->where('role', $role);
    }

    #[Filter('status')]
    public function ofStatus(string $status): static
    {
        return $this->where('status', $status);
    }

    #[Filter('is_new')]
    public function isNew(): static
    {
        return $this->where('created_at', '>', now()->subDays(1));
    }
}
