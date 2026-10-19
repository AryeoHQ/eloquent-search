<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\OpenSearch;

use DirectoryTree\OpenSearchAdapter;
use DirectoryTree\OpenSearchAdapter\Indices\IndexManager;
use DirectoryTree\OpenSearchClient\Facades\OpenSearch;
use DirectoryTree\OpenSearchMigrations;
use DirectoryTree\OpenSearchMigrations\Facades\Index;

final class UsesNonStrictIndexManagers
{
    public function handle(): void
    {
        new IndexManager(OpenSearch::default());
        new OpenSearchAdapter\Testing\Fakes\FakeIndexManager;
        new OpenSearchMigrations\Testing\Fakes\FakeIndexManager;
        Index::fake();
        Index::create('posts');
    }
}
