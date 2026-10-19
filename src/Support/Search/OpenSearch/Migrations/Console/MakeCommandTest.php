<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Migrations\Console;

use DirectoryTree\OpenSearchMigrations\MigrationInterface;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Search\OpenSearch\Migrations\Console\References\Migration;
use Support\Search\OpenSearch\Migrations\Facades\Index;
use Tests\TestCase;

#[CoversClass(MakeCommand::class)]
class MakeCommandTest extends TestCase
{
    #[Test]
    public function it_generates_a_migration_that_uses_our_index_facade(): void
    {
        $this->freezeTime();
        File::fake(config('opensearch-migrations.storage_directory').'*');

        $this->artisan(MakeCommand::class, ['name' => 'create_posts_index'])->assertSuccessful();

        $migration = File::get((new Migration('create_posts_index'))->filePath->toString());

        $this->assertStringContainsString('use '.Index::class.';', $migration);
        $this->assertStringContainsString('return new class implements '.class_basename(MigrationInterface::class), $migration);
    }
}
