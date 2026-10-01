<?php

declare(strict_types=1);

namespace Tooling\EloquentSearch\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
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
        $this->analyse([$this->getFixturePath('ValidSearchable.php')], []);
    }

    #[Test]
    public function it_passes_when_class_does_not_implement_searchable(): void
    {
        $this->analyse([$this->getFixturePath('NonBuilderClass.php')], []);
    }

    #[Test]
    public function it_fails_when_searchable_does_not_use_interacts_with_search_engine(): void
    {
        $this->analyse([$this->getFixturePath('SearchableWithoutInteracts.php')], [
            [
                'Searchable must use InteractsWithSearchEngine.',
                10,
            ],
        ]);
    }
}
