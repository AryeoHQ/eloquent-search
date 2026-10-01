<?php

declare(strict_types=1);

namespace Support\Search\Scout;

use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;
use ReflectionClass;
use Support\Search\Scout\Attributes\ScoutConnection;
use Support\Search\Scout\Attributes\ScoutQueue;
use Support\Search\Scout\Attributes\UseScoutBuilder;

trait InteractsWithSearchEngine
{
    use Searchable;

    /**
     * @var array<class-string, array{builder: ?string, queue: ?string, connection: ?string}>
     */
    private static array $searchAttributeCache = [];

    public function syncWithSearchUsing(): string
    {
        return $this->resolveSearchAttributes()['queue']
            ?? config('scout.queue.queue')
            ?? 'default';
    }

    public function syncWithSearchUsingQueue(): string
    {
        return $this->resolveSearchAttributes()['connection']
            ?? config('scout.queue.connection')
            ?? config('queue.default')
            ?? 'sync';
    }

    /**
     * @return class-string<Builder>
     */
    public function makeSearchableUsing(): string
    {
        return $this->resolveSearchAttributes()['builder']
            ?? Builder::class;
    }

    /**
     * @return array{builder: ?string, queue: ?string, connection: ?string}
     */
    private function resolveSearchAttributes(): array
    {
        $class = static::class;

        if (isset(self::$searchAttributeCache[$class])) {
            return self::$searchAttributeCache[$class];
        }

        $reflection = new ReflectionClass($class);

        $builder = null;
        $builderAttributes = $reflection->getAttributes(UseScoutBuilder::class);
        if ($builderAttributes !== []) {
            /** @var UseScoutBuilder $attr */
            $attr = $builderAttributes[0]->newInstance();
            $builder = $attr->builder;
        }

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

        return self::$searchAttributeCache[$class] = compact('builder', 'queue', 'connection');
    }
}
