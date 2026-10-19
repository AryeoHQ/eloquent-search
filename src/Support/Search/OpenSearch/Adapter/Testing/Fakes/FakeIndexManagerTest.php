<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Adapter\Testing\Fakes;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Search\OpenSearch\Adapter\Provides\EnforcesStrictMapping;
use Tests\TestCase;

#[CoversClass(FakeIndexManager::class)]
class FakeIndexManagerTest extends TestCase
{
    #[Test]
    public function it_enforces_strict_mapping(): void
    {
        $this->assertContains(EnforcesStrictMapping::class, class_uses_recursive(FakeIndexManager::class));
    }
}
