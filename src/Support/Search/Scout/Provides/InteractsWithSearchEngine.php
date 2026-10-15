<?php

declare(strict_types=1);

namespace Support\Search\Scout\Provides;

use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Support\Search\Scout\Attributes\Reindex;
use Support\Search\Scout\Attributes\ScoutConnection;
use Support\Search\Scout\Attributes\ScoutQueue;
use Support\Search\Scout\Attributes\UseScoutBuilder;

trait InteractsWithSearchEngine
{
    use \Laravel\Scout\Searchable {
        search as protected defaultSearch;
        syncWithSearchUsing as protected defaultSyncWithSearchUsing;
        syncWithSearchUsingQueue as protected defaultSyncWithSearchUsingQueue;
    }

    /** @var array<class-string, array<class-string, string|null>> */
    protected static array $resolvedScoutAttributes = [];

    /** @var class-string<\Laravel\Scout\Builder<*>>|null */
    protected static null|string $scoutBuilder = null;

    /**
     * @param  string  $query
     * @param  \Closure|null  $callback
     * @return \Laravel\Scout\Builder<static>
     */
    public static function search($query = '', $callback = null)
    {
        // Set on every call, since subclasses share this property with their parent.
        static::$scoutBuilder = static::resolveScoutAttribute(UseScoutBuilder::class, 'class');

        return static::defaultSearch($query, $callback);
    }

    /** @return string|null */
    public function syncWithSearchUsing()
    {
        return static::resolveScoutAttribute(ScoutConnection::class, 'name') ?? $this->defaultSyncWithSearchUsing();
    }

    /** @return string|null */
    public function syncWithSearchUsingQueue()
    {
        return static::resolveScoutAttribute(ScoutQueue::class, 'name') ?? $this->defaultSyncWithSearchUsingQueue();
    }

    /**
     * @param  class-string  $attribute
     */
    protected static function resolveScoutAttribute(string $attribute, string $property): null|string
    {
        if (array_key_exists($attribute, static::$resolvedScoutAttributes[static::class] ?? [])) {
            return static::$resolvedScoutAttributes[static::class][$attribute];
        }

        $attributes = (new ReflectionClass(static::class))->getAttributes($attribute);

        return static::$resolvedScoutAttributes[static::class][$attribute] = $attributes === []
            ? null
            : $attributes[0]->newInstance()->{$property};
    }

    /** @return Collection<int, Reindex> */
    public static function reindexes(): Collection
    {
        return collect((new ReflectionMethod(static::class, 'toSearchableArray'))->getAttributes(Reindex::class))
            ->map(fn (ReflectionAttribute $attribute): Reindex => $attribute->newInstance());
    }
}
