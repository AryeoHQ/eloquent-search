<?php

declare(strict_types=1);

namespace Tooling\Search\PhpStan\Database;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Tooling\Concerns\GetsFixtures;

/** @extends RuleTestCase<HasFiltersMustImplementFilterable> */
#[CoversClass(HasFiltersMustImplementFilterable::class)]
class HasFiltersMustImplementFilterableTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new HasFiltersMustImplementFilterable;
    }

    #[Test]
    public function it_passes_when_has_filters_implements_filterable(): void
    {
        $this->analyse([$this->getFixturePath('Database/ValidBuilder.php')], []);
    }

    #[Test]
    public function it_passes_when_class_does_not_use_has_filters(): void
    {
        $this->analyse([$this->getFixturePath('Database/NonBuilderClass.php')], []);
    }

    #[Test]
    public function it_passes_when_has_filters_is_not_on_builder(): void
    {
        $this->analyse([$this->getFixturePath('Database/HasFiltersOnNonBuilder.php')], []);
    }

    #[Test]
    public function it_fails_when_has_filters_does_not_implement_filterable(): void
    {
        $this->analyse([$this->getFixturePath('Database/HasFiltersWithoutFilterable.php')], [
            [
                'HasFilters must implement Filterable.',
                15,
            ],
        ]);
    }
}
