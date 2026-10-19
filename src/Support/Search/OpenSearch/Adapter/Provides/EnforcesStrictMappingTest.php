<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Adapter\Provides;

use DirectoryTree\OpenSearchAdapter\Indices\IndexBlueprint;
use DirectoryTree\OpenSearchAdapter\Indices\Mapping;
use DirectoryTree\OpenSearchAdapter\Indices\Settings;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Support\Search\OpenSearch\Adapter\Testing\Fakes\FakeIndexManager;
use Tests\TestCase;

#[CoversTrait(EnforcesStrictMapping::class)]
class EnforcesStrictMappingTest extends TestCase
{
    #[Test]
    public function it_creates_an_index_with_a_strict_mapping(): void
    {
        $fake = new FakeIndexManager;

        $fake->create(new IndexBlueprint('posts'));

        $fake->assertCreated(
            'posts',
            fn (IndexBlueprint $index): bool => $index->mapping()?->toArray() === ['dynamic' => 'strict']
        );
    }

    #[Test]
    #[TestWith([true])]
    #[TestWith([false])]
    public function it_overrides_the_dynamic_value_a_mapping_sets(bool $dynamic): void
    {
        $fake = new FakeIndexManager;

        $fake->create(new IndexBlueprint('posts', (new Mapping)->dynamic($dynamic)->keyword('id')));

        $fake->assertCreated(
            'posts',
            fn (IndexBlueprint $index): bool => $index->mapping()?->toArray() === [
                'dynamic' => 'strict',
                'properties' => ['id' => ['type' => 'keyword']],
            ]
        );
    }

    #[Test]
    public function it_keeps_the_index_settings(): void
    {
        $fake = new FakeIndexManager;

        $fake->create(
            new IndexBlueprint('posts', settings: (new Settings)->index(['number_of_shards' => 1]))
        );

        $fake->assertCreated(
            'posts',
            fn (IndexBlueprint $index): bool => $index->settings()?->toArray() === ['index' => ['number_of_shards' => 1]]
        );
    }

    #[Test]
    public function it_puts_a_strict_mapping(): void
    {
        $fake = new FakeIndexManager;

        $fake->putMapping('posts', (new Mapping)->dynamic(true));

        $fake->assertMappingPut(
            'posts',
            fn (Mapping $mapping): bool => $mapping->toArray() === ['dynamic' => 'strict']
        );
    }
}
