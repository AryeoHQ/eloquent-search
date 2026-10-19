<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Migrations\Testing\Fakes;

use DirectoryTree\OpenSearchMigrations;
use DirectoryTree\OpenSearchMigrations\Adapters\IndexManagerAdapter;
use DirectoryTree\OpenSearchMigrations\Support\MigrationPrefix;
use Support\Search\OpenSearch\Adapter;

class FakeIndexManager extends OpenSearchMigrations\Testing\Fakes\FakeIndexManager
{
    /**
     * @param  array<int, string>  $existing
     */
    public function __construct(array $existing = [])
    {
        parent::__construct($existing);

        $this->manager = new Adapter\Testing\Fakes\FakeIndexManager(existing: array_map(MigrationPrefix::index(...), $existing));
        $this->adapter = new IndexManagerAdapter($this->manager);
    }
}
