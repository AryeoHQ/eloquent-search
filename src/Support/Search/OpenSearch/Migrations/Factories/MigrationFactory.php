<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Migrations\Factories;

use DirectoryTree\OpenSearchMigrations;
use DirectoryTree\OpenSearchMigrations\Filesystem\MigrationFile;
use DirectoryTree\OpenSearchMigrations\MigrationInterface;

class MigrationFactory extends OpenSearchMigrations\Factories\MigrationFactory
{
    public function makeFromFile(MigrationFile $file): MigrationInterface
    {
        // Not require_once: refresh loads each file twice in one run.
        return require $file->path();
    }
}
