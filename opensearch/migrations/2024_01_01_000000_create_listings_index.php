<?php

declare(strict_types=1);

use DirectoryTree\OpenSearchAdapter\Indices\Mapping;
use DirectoryTree\OpenSearchMigrations\Facades\Index;
use DirectoryTree\OpenSearchMigrations\MigrationInterface;

class CreateListingsIndex implements MigrationInterface
{
    public function up(): void
    {
        Index::create('listings', function (Mapping $mapping) {
            $mapping->keyword('id');
            $mapping->text('address');
        });
    }

    public function down(): void
    {
        Index::dropIfExists('listings');
    }
}
