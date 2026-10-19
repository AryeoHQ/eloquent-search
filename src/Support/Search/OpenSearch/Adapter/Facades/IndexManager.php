<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Adapter\Facades;

use DirectoryTree\OpenSearchAdapter\Indices\IndexManagerInterface;
use Illuminate\Support\Facades\Facade;
use Support\Search\OpenSearch\Adapter\Testing\Fakes\FakeIndexManager;

/**
 * @see \DirectoryTree\OpenSearchAdapter\Indices\IndexManagerInterface
 */
class IndexManager extends Facade
{
    public static function fake(): FakeIndexManager
    {
        static::swap($fake = new FakeIndexManager);

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return IndexManagerInterface::class;
    }
}
