<?php

declare(strict_types=1);

namespace Tooling\Search\PhpStan\Database;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Tooling\Concerns\GetsFixtures;

/** @extends RuleTestCase<FilterableMustUseHasFilters> */
#[CoversClass(FilterableMustUseHasFilters::class)]
class FilterableMustUseHasFiltersTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new FilterableMustUseHasFilters;
    }

    #[Test]
    public function it_passes_when_filterable_uses_has_filters(): void
    {
        $this->analyse([$this->getFixturePath('Database/ValidBuilder.php')], []);
    }

    #[Test]
    public function it_passes_when_class_does_not_implement_filterable(): void
    {
        $this->analyse([$this->getFixturePath('Database/NonBuilderClass.php')], []);
    }

    #[Test]
    public function it_passes_when_filterable_is_not_on_builder(): void
    {
        $this->analyse([$this->getFixturePath('Database/FilterableOnNonBuilder.php')], []);
    }

    #[Test]
    public function it_fails_when_filterable_does_not_use_has_filters(): void
    {
        $this->analyse([$this->getFixturePath('Database/FilterableWithoutHasFilters.php')], [
            [
                'Filterable must use HasFilters.',
                15,
            ],
        ]);
    }
}
