<?php

declare(strict_types=1);

namespace Tooling\EloquentSearch\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Tooling\Concerns\GetsFixtures;

/** @extends RuleTestCase<FilterableMustOnlyBeOnBuilder> */
#[CoversClass(FilterableMustOnlyBeOnBuilder::class)]
class FilterableMustOnlyBeOnBuilderTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new FilterableMustOnlyBeOnBuilder;
    }

    #[Test]
    public function it_passes_when_filterable_is_on_builder(): void
    {
        $this->analyse([$this->getFixturePath('ValidBuilder.php')], []);
    }

    #[Test]
    public function it_passes_when_class_does_not_implement_filterable(): void
    {
        $this->analyse([$this->getFixturePath('NonBuilderClass.php')], []);
    }

    #[Test]
    public function it_fails_when_filterable_is_not_on_builder(): void
    {
        $this->analyse([$this->getFixturePath('FilterableOnNonBuilder.php')], [
            [
                'Filterable must only be on Builder.',
                9,
            ],
        ]);
    }
}
