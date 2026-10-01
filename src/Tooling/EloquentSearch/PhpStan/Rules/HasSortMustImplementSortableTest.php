<?php

declare(strict_types=1);

namespace Tooling\EloquentSearch\PhpStan\Rules;

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
        $this->analyse([$this->getFixturePath('ValidBuilder.php')], []);
    }

    #[Test]
    public function it_passes_when_class_does_not_use_has_sort(): void
    {
        $this->analyse([$this->getFixturePath('NonBuilderClass.php')], []);
    }

    #[Test]
    public function it_passes_when_has_sort_is_not_on_builder(): void
    {
        $this->analyse([$this->getFixturePath('HasSortOnNonBuilder.php')], []);
    }

    #[Test]
    public function it_fails_when_has_sort_does_not_implement_sortable(): void
    {
        $this->analyse([$this->getFixturePath('HasSortWithoutSortable.php')], [
            [
                'HasSort must implement Sortable.',
                15,
            ],
        ]);
    }
}
