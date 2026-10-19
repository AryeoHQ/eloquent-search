<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Adapter\Facades;

use DirectoryTree\OpenSearchAdapter\Indices\IndexManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(IndexManager::class)]
class IndexManagerTest extends TestCase
{
    #[Test]
    public function fake_replaces_the_bound_index_manager(): void
    {
        $fake = IndexManager::fake();

        $this->assertSame($fake, $this->app->make(IndexManagerInterface::class));
    }
}
