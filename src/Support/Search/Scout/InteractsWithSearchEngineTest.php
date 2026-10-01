<?php

declare(strict_types=1);

namespace Support\Search\Scout;

use Laravel\Scout\Builder;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\Fixtures\Support\BareSearchableModel;
use Tests\Fixtures\Support\SearchableModel;
use Tests\TestCase;

#[CoversTrait(InteractsWithSearchEngine::class)]
class InteractsWithSearchEngineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([SearchableModel::class, BareSearchableModel::class] as $class) {
            $prop = (new ReflectionClass($class))->getProperty('searchAttributeCache');
            $prop->setValue(null, []);
        }
    }

    #[Test]
    public function it_reads_scout_queue_from_attribute(): void
    {
        $model = new SearchableModel;

        $this->assertSame('test-queue', $model->syncWithSearchUsing());
    }

    #[Test]
    public function it_reads_scout_connection_from_attribute(): void
    {
        $model = new SearchableModel;

        $this->assertSame('test-connection', $model->syncWithSearchUsingQueue());
    }

    #[Test]
    public function it_reads_scout_builder_from_attribute(): void
    {
        $model = new SearchableModel;

        $this->assertSame(Builder::class, $model->makeSearchableUsing());
    }

    #[Test]
    public function it_falls_back_to_scout_config_queue_when_no_attribute(): void
    {
        config()->set('scout.queue.queue', 'fallback-queue');

        $model = new BareSearchableModel;

        $this->assertSame('fallback-queue', $model->syncWithSearchUsing());
    }

    #[Test]
    public function it_falls_back_to_default_queue_when_no_config_and_no_attribute(): void
    {
        config()->set('scout.queue.queue', null);

        $model = new BareSearchableModel;

        $this->assertSame('default', $model->syncWithSearchUsing());
    }

    #[Test]
    public function it_falls_back_to_scout_builder_when_no_attribute(): void
    {
        $model = new BareSearchableModel;

        $this->assertSame(Builder::class, $model->makeSearchableUsing());
    }

    #[Test]
    public function it_caches_attributes_per_class(): void
    {
        $model = new SearchableModel;
        $model->syncWithSearchUsing();

        $cache = (new ReflectionClass(SearchableModel::class))
            ->getProperty('searchAttributeCache');

        $this->assertArrayHasKey(SearchableModel::class, $cache->getValue(null));
    }
}
