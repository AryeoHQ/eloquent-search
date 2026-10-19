<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Adapter\Facades;

use DirectoryTree\OpenSearchAdapter\Documents\DocumentManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(DocumentManager::class)]
class DocumentManagerTest extends TestCase
{
    #[Test]
    public function fake_replaces_the_bound_document_manager(): void
    {
        $fake = DocumentManager::fake();

        $this->assertSame(
            $fake,
            $this->app->make(DocumentManagerInterface::class)
        );
    }
}
