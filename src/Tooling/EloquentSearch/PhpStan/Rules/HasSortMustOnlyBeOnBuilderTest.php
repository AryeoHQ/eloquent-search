<?php

declare(strict_types=1);

namespace Tooling\EloquentSearch\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Tooling\Concerns\GetsFixtures;

/** @extends RuleTestCase<HasSortMustOnlyBeOnBuilder> */
#[CoversClass(HasSortMustOnlyBeOnBuilder::class)]
class HasSortMustOnlyBeOnBuilderTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new HasSortMustOnlyBeOnBuilder;
    }

    #[Test]
    public function it_passes_when_has_sort_is_on_builder(): void
    {
        $this->analyse([$this->getFixturePath('ValidBuilder.php')], []);
    }

    #[Test]
    public function it_passes_when_class_does_not_use_has_sort(): void
    {
        $this->analyse([$this->getFixturePath('NonBuilderClass.php')], []);
    }

    #[Test]
    public function it_fails_when_has_sort_is_not_on_builder(): void
    {
        $this->analyse([$this->getFixturePath('HasSortOnNonBuilder.php')], [
            [
                'HasSort must only be on Builder.',
                9,
            ],
        ]);
    }
}
