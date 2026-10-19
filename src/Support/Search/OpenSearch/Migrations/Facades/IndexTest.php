<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Migrations\Facades;

use DirectoryTree\OpenSearchAdapter\Indices\Mapping;
use DirectoryTree\OpenSearchMigrations\IndexManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(Index::class)]
class IndexTest extends TestCase
{
    #[Test]
    public function fake_replaces_the_bound_index_manager(): void
    {
        $fake = Index::fake();

        $this->assertSame($fake, $this->app->make(IndexManagerInterface::class));
    }

    #[Test]
    public function fake_records_a_strict_mapping(): void
    {
        $fake = Index::fake();

        Index::create('posts');

        $fake->assertCreated('posts', fn (null|Mapping $mapping): bool => $mapping?->toArray() === ['dynamic' => 'strict']);
    }

    #[Test]
    public function fake_knows_the_existing_indices(): void
    {
        $fake = Index::fake(['posts']);

        Index::createIfNotExists('posts');

        $fake->assertChecked('posts')->assertNotCreated('posts');
    }
}
