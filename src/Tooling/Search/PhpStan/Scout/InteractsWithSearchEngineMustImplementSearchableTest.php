<?php

declare(strict_types=1);

namespace Tooling\Search\PhpStan\Scout;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Search\Scout\Contracts\Searchable;
use Support\Search\Scout\Provides\InteractsWithSearchEngine;
use Tests\Fixtures\Tooling\Concerns\GetsFixtures;

/** @extends RuleTestCase<InteractsWithSearchEngineMustImplementSearchable> */
#[CoversClass(InteractsWithSearchEngineMustImplementSearchable::class)]
class InteractsWithSearchEngineMustImplementSearchableTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new InteractsWithSearchEngineMustImplementSearchable;
    }

    #[Test]
    public function it_passes_when_interacts_with_search_engine_implements_searchable(): void
    {
        $this->analyse([$this->getFixturePath('Scout/ValidSearchableModel.php')], []);
    }

    #[Test]
    public function it_fails_when_interacts_with_search_engine_does_not_implement_searchable(): void
    {
        $this->analyse([$this->getFixturePath('Scout/InteractsWithSearchEngineWithoutSearchable.php')], [
            [
                class_basename(InteractsWithSearchEngine::class).' must implement '.class_basename(Searchable::class).'.',
                10,
            ],
        ]);
    }
}
