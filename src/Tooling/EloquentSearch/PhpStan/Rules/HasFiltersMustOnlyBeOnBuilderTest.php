<?php

declare(strict_types=1);

namespace Tooling\EloquentSearch\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Tooling\Concerns\GetsFixtures;

/** @extends RuleTestCase<HasFiltersMustOnlyBeOnBuilder> */
#[CoversClass(HasFiltersMustOnlyBeOnBuilder::class)]
class HasFiltersMustOnlyBeOnBuilderTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new HasFiltersMustOnlyBeOnBuilder;
    }

    #[Test]
    public function it_passes_when_has_filters_is_on_builder(): void
    {
        $this->analyse([$this->getFixturePath('ValidBuilder.php')], []);
    }

    #[Test]
    public function it_passes_when_class_does_not_use_has_filters(): void
    {
        $this->analyse([$this->getFixturePath('NonBuilderClass.php')], []);
    }

    #[Test]
    public function it_fails_when_has_filters_is_not_on_builder(): void
    {
        $this->analyse([$this->getFixturePath('HasFiltersOnNonBuilder.php')], [
            [
                'HasFilters must only be on Builder.',
                9,
            ],
        ]);
    }
}
