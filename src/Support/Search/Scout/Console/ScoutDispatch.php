<?php

declare(strict_types=1);

namespace Support\Search\Scout\Console;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Thin shim that calls Scout Searchable trait methods whose dynamic binding
 * PHPStan cannot verify through normal static analysis.
 *
 * This file is excluded from PHPStan analysis (phpstan.package.neon).
 * All public method signatures here serve as the static contracts for callers.
 */
final class ScoutDispatch
{
    /**
     * @param  class-string<Model>  $class
     * @return Builder<Model>
     */
    public static function makeAllSearchableQuery(string $class): Builder
    {
        return $class::makeAllSearchableQuery();
    }

    public static function searchableAs(Model $model): string
    {
        return $model->searchableAs();
    }

    public static function getScoutKeyName(Model $model): string
    {
        $key = $model->getScoutKeyName();

        return is_string($key) ? $key : $model->getKeyName();
    }

    public static function shouldBeSearchable(Model $model): bool
    {
        return $model->shouldBeSearchable();
    }
}
