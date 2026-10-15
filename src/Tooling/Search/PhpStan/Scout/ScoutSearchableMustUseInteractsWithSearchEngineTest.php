<?php

declare(strict_types=1);

namespace Tooling\Search\PhpStan\Scout;

use Laravel\Scout\Searchable as ScoutSearchable;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Search\Scout\Provides\InteractsWithSearchEngine;
use Tests\Fixtures\Tooling\Concerns\GetsFixtures;

/** @extends RuleTestCase<ScoutSearchableMustUseInteractsWithSearchEngine> */
#[CoversClass(ScoutSearchableMustUseInteractsWithSearchEngine::class)]
class ScoutSearchableMustUseInteractsWithSearchEngineTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new ScoutSearchableMustUseInteractsWithSearchEngine;
    }

    #[Test]
    public function it_passes_when_scouts_trait_comes_through_interacts_with_search_engine(): void
    {
        $this->analyse([$this->getFixturePath('Scout/ValidSearchableModel.php')], []);
    }

    #[Test]
    public function it_fails_when_scouts_trait_is_used_directly(): void
    {
        $this->analyse([$this->getFixturePath('Scout/ScoutSearchableModel.php')], [
            [
                ScoutSearchable::class.' must be used through '.class_basename(InteractsWithSearchEngine::class).'.',
                10,
            ],
        ]);
    }
}
