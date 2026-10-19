<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Migrations\Factories;

use DirectoryTree\OpenSearchMigrations\Filesystem\MigrationFile;
use DirectoryTree\OpenSearchMigrations\MigrationInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(MigrationFactory::class)]
class MigrationFactoryTest extends TestCase
{
    #[Test]
    public function it_makes_the_migration_the_file_returns(): void
    {
        $file = new MigrationFile(
            __DIR__.'/../../../../../../tests/Fixtures/Support/OpenSearch/Migrations/2026_01_01_000000_create_posts_index.php'
        );

        $this->assertInstanceOf(MigrationInterface::class, (new MigrationFactory)->makeFromFile($file));
    }

    #[Test]
    public function it_makes_a_new_migration_each_time_the_file_is_loaded(): void
    {
        $file = new MigrationFile(
            __DIR__.'/../../../../../../tests/Fixtures/Support/OpenSearch/Migrations/2026_01_01_000000_create_posts_index.php');

        $factory = new MigrationFactory;

        $this->assertNotSame($factory->makeFromFile($file), $factory->makeFromFile($file));
    }
}
