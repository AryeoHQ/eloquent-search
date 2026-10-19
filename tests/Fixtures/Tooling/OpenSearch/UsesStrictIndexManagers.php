<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\OpenSearch;

use DirectoryTree\OpenSearchClient\Facades\OpenSearch;
use Support\Search\OpenSearch\Adapter;
use Support\Search\OpenSearch\Adapter\Indices\IndexManager;
use Support\Search\OpenSearch\Migrations;
use Support\Search\OpenSearch\Migrations\Facades\Index;

final class UsesStrictIndexManagers
{
    public function handle(): void
    {
        new IndexManager(OpenSearch::default());
        new Adapter\Testing\Fakes\FakeIndexManager;
        new Migrations\Testing\Fakes\FakeIndexManager;
        Index::fake();
        Index::create('posts');
    }
}
