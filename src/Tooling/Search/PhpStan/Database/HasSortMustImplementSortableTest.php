<?php

declare(strict_types=1);

namespace Tooling\Search\PhpStan\Database;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Tooling\Concerns\GetsFixtures;

/** @extends RuleTestCase<HasSortMustImplementSortable> */
#[CoversClass(HasSortMustImplementSortable::class)]
class HasSortMustImplementSortableTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new HasSortMustImplementSortable;
    }

    #[Test]
    public function it_passes_when_has_sort_implements_sortable(): void
    {
        $this->analyse([$this->getFixturePath('Database/ValidBuilder.php')], []);
    }

    #[Test]
    public function it_passes_when_class_does_not_use_has_sort(): void
    {
        $this->analyse([$this->getFixturePath('Database/NonBuilderClass.php')], []);
    }

    #[Test]
    public function it_passes_when_has_sort_is_not_on_builder(): void
    {
        $this->analyse([$this->getFixturePath('Database/HasSortOnNonBuilder.php')], []);
    }

    #[Test]
    public function it_fails_when_has_sort_does_not_implement_sortable(): void
    {
        $this->analyse([$this->getFixturePath('Database/HasSortWithoutSortable.php')], [
            [
                'HasSort must implement Sortable.',
                15,
            ],
        ]);
    }
}
