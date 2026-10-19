<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Adapter\Facades;

use DirectoryTree\OpenSearchAdapter\Documents\DocumentManagerInterface;
use DirectoryTree\OpenSearchAdapter\Testing\Fakes\FakeDocumentManager;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DirectoryTree\OpenSearchAdapter\Documents\DocumentManagerInterface
 */
class DocumentManager extends Facade
{
    public static function fake(): FakeDocumentManager
    {
        static::swap($fake = new FakeDocumentManager);

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return DocumentManagerInterface::class;
    }
}
