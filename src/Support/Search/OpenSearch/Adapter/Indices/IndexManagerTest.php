<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Adapter\Indices;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Search\OpenSearch\Adapter\Provides\EnforcesStrictMapping;
use Tests\TestCase;

#[CoversClass(IndexManager::class)]
class IndexManagerTest extends TestCase
{
    #[Test]
    public function it_enforces_strict_mapping(): void
    {
        $this->assertContains(EnforcesStrictMapping::class, class_uses_recursive(IndexManager::class));
    }
}
