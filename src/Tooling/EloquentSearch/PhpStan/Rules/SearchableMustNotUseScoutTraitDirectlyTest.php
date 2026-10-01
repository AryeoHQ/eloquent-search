<?php

declare(strict_types=1);

namespace Tooling\EloquentSearch\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Tooling\Concerns\GetsFixtures;

/** @extends RuleTestCase<SearchableMustNotUseScoutTraitDirectly> */
#[CoversClass(SearchableMustNotUseScoutTraitDirectly::class)]
class SearchableMustNotUseScoutTraitDirectlyTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new SearchableMustNotUseScoutTraitDirectly;
    }

    #[Test]
    public function it_passes_when_model_uses_interacts_with_search_engine(): void
    {
        $this->analyse([$this->getFixturePath('ValidSearchable.php')], []);
    }

    #[Test]
    public function it_passes_when_class_does_not_use_scout_searchable(): void
    {
        $this->analyse([$this->getFixturePath('NonBuilderClass.php')], []);
    }

    #[Test]
    public function it_fails_when_model_uses_scout_trait_directly(): void
    {
        $this->analyse([$this->getFixturePath('ScoutTraitDirectly.php')], [
            [
                'Use InteractsWithSearchEngine instead of Laravel\Scout\Searchable directly.',
                10,
            ],
        ]);
    }
}
