<?php

declare(strict_types=1);

namespace Support\Search\Scout;

use Laravel\Scout\Searchable;
use ReflectionClass;
use Support\Search\Scout\Attributes\ScoutConnection;
use Support\Search\Scout\Attributes\ScoutQueue;
use Support\Search\Scout\Attributes\UseScoutBuilder;

trait InteractsWithSearchEngine
{
    use Searchable;

    protected static ?string $scoutBuilder = null;

    /**
     * @var array<class-string, array{queue: ?string, connection: ?string}>
     */
    private static array $searchAttributeCache = [];

    public static function bootInteractsWithSearchEngine(): void
    {
        $attributes = (new ReflectionClass(static::class))->getAttributes(UseScoutBuilder::class);

        if ($attributes !== []) {
            /** @var UseScoutBuilder $attr */
            $attr = $attributes[0]->newInstance();
            static::$scoutBuilder = $attr->builder;
        }
    }

    // syncWithSearchUsing() → queue connection  (used in ->onConnection())
    public function syncWithSearchUsing(): string
    {
        return $this->resolveSearchAttributes()['connection']
            ?? config('scout.queue.connection')
            ?? config('queue.default')
            ?? 'sync';
    }

    // syncWithSearchUsingQueue() → queue name  (used in ->onQueue())
    public function syncWithSearchUsingQueue(): string
    {
        return $this->resolveSearchAttributes()['queue']
            ?? config('scout.queue.queue')
            ?? 'default';
    }

    /**
     * @return array{queue: ?string, connection: ?string}
     */
    private function resolveSearchAttributes(): array
    {
        $class = static::class;

        if (isset(self::$searchAttributeCache[$class])) {
            return self::$searchAttributeCache[$class];
        }

        $reflection = new ReflectionClass($class);

        $queue = null;
        $queueAttributes = $reflection->getAttributes(ScoutQueue::class);
        if ($queueAttributes !== []) {
            /** @var ScoutQueue $attr */
            $attr = $queueAttributes[0]->newInstance();
            $queue = $attr->queue;
        }

        $connection = null;
        $connectionAttributes = $reflection->getAttributes(ScoutConnection::class);
        if ($connectionAttributes !== []) {
            /** @var ScoutConnection $attr */
            $attr = $connectionAttributes[0]->newInstance();
            $connection = $attr->connection;
        }

        return self::$searchAttributeCache[$class] = compact('queue', 'connection');
    }
}
