<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Migrations\Facades;

use DirectoryTree\OpenSearchMigrations;
use Support\Search\OpenSearch\Migrations\Testing\Fakes\FakeIndexManager;

class Index extends OpenSearchMigrations\Facades\Index
{
    /**
     * @param  array<int, string>  $existing
     */
    public static function fake(array $existing = []): FakeIndexManager
    {
        static::swap($fake = new FakeIndexManager($existing));

        return $fake;
    }
}
