<?php

declare(strict_types=1);

namespace Tooling\Search\PhpStan\Scout;

use Laravel\Scout\Builder;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Search\Scout\Attributes\UseScoutBuilder;
use Tests\Fixtures\Tooling\Concerns\GetsFixtures;

/** @extends RuleTestCase<UseScoutBuilderMustReferenceScoutBuilder> */
#[CoversClass(UseScoutBuilderMustReferenceScoutBuilder::class)]
class UseScoutBuilderMustReferenceScoutBuilderTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new UseScoutBuilderMustReferenceScoutBuilder;
    }

    #[Test]
    public function it_passes_when_use_scout_builder_references_a_scout_builder(): void
    {
        $this->analyse([$this->getFixturePath('Scout/ValidSearchableModel.php')], []);
    }

    #[Test]
    public function it_fails_when_use_scout_builder_references_something_else(): void
    {
        $this->analyse([$this->getFixturePath('Scout/UseScoutBuilderWithNonBuilder.php')], [
            [
                class_basename(UseScoutBuilder::class).' must reference '.Builder::class.'.',
                13,
            ],
        ]);
    }
}
