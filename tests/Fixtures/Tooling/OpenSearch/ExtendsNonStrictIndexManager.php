<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\OpenSearch;

use DirectoryTree\OpenSearchAdapter\Indices\IndexBlueprint;
use DirectoryTree\OpenSearchAdapter\Indices\IndexManager;

final class ExtendsNonStrictIndexManager extends IndexManager
{
    public function create(IndexBlueprint $index): static
    {
        return parent::create($index);
    }

    public function binding(): string
    {
        return IndexManager::class;
    }
}
