<?php

declare(strict_types=1);

namespace Tooling\Search\PhpStan\Scout;

use Illuminate\Database\Eloquent\Model;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Search\Scout\Provides\InteractsWithSearchEngine;
use Tests\Fixtures\Tooling\Concerns\GetsFixtures;

/** @extends RuleTestCase<InteractsWithSearchEngineMustOnlyBeOnModel> */
#[CoversClass(InteractsWithSearchEngineMustOnlyBeOnModel::class)]
class InteractsWithSearchEngineMustOnlyBeOnModelTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new InteractsWithSearchEngineMustOnlyBeOnModel;
    }

    #[Test]
    public function it_passes_when_interacts_with_search_engine_is_on_a_model(): void
    {
        $this->analyse([$this->getFixturePath('Scout/ValidSearchableModel.php')], []);
    }

    #[Test]
    public function it_fails_when_interacts_with_search_engine_is_not_on_a_model(): void
    {
        $this->analyse([$this->getFixturePath('Scout/InteractsWithSearchEngineOnNonModel.php')], [
            [
                class_basename(InteractsWithSearchEngine::class).' must only be on '.class_basename(Model::class).'.',
                10,
            ],
        ]);
    }
}
