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

/** @extends RuleTestCase<SearchableMustUseInteractsWithSearchEngine> */
#[CoversClass(SearchableMustUseInteractsWithSearchEngine::class)]
class SearchableMustUseInteractsWithSearchEngineTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new SearchableMustUseInteractsWithSearchEngine;
    }

    #[Test]
    public function it_passes_when_searchable_uses_interacts_with_search_engine(): void
    {
        $this->analyse([$this->getFixturePath('Scout/ValidSearchableModel.php')], []);
    }

    #[Test]
    public function it_fails_when_searchable_does_not_use_interacts_with_search_engine(): void
    {
        $this->analyse([$this->getFixturePath('Scout/SearchableWithoutInteractsWithSearchEngine.php')], [
            [
                class_basename(Searchable::class).' must use '.class_basename(InteractsWithSearchEngine::class).'.',
                10,
            ],
        ]);
    }
}
