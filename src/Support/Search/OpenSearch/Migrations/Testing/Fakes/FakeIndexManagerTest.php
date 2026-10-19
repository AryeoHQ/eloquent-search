<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Migrations\Testing\Fakes;

use DirectoryTree\OpenSearchAdapter\Indices\Mapping;
use Orchestra\Testbench\Attributes\WithConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(FakeIndexManager::class)]
class FakeIndexManagerTest extends TestCase
{
    #[Test]
    public function it_records_a_strict_mapping_for_a_created_index(): void
    {
        $fake = new FakeIndexManager;

        $fake->create('posts', fn (Mapping $mapping) => $mapping->dynamic(true));

        $fake->assertCreated('posts', fn (null|Mapping $mapping): bool => $mapping?->toArray() === ['dynamic' => 'strict']);
    }

    #[Test]
    #[WithConfig('opensearch-migrations.index_name_prefix', 'prefix_')]
    public function it_does_not_create_an_index_that_already_exists(): void
    {
        $fake = new FakeIndexManager(['posts']);

        $fake->createIfNotExists('posts');

        $fake->assertChecked('posts')->assertNotCreated('posts');
    }
}
