<?php

declare(strict_types=1);

namespace Support\Search\Scout\Contracts;

/**
 * @phpstan-require-extends \Illuminate\Database\Eloquent\Model
 */
interface Searchable
{
    /**
     * @param  string  $query
     * @param  \Closure|null  $callback
     * @return \Laravel\Scout\Builder<static&\Illuminate\Database\Eloquent\Model>
     */
    public static function search($query = '', $callback = null);

    /** @return string */
    public function searchableAs();

    /** @return array<string, mixed> */
    public function toSearchableArray();

    /** @return mixed */
    public function getScoutKey();

    /** @return string */
    public function getScoutKeyName();

    /** @return \Laravel\Scout\Engines\Engine */
    public function searchableUsing();

    /** @return bool */
    public function shouldBeSearchable();

    /** @return string|null */
    public function syncWithSearchUsing();

    /** @return string|null */
    public function syncWithSearchUsingQueue();

    /** @return \Illuminate\Support\Collection<int, \Support\Search\Scout\Attributes\Reindex> */
    public static function reindexes();
}
