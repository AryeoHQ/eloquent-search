<?php

declare(strict_types=1);

namespace Tooling\Search\PhpStan\Scout;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Search\Scout\Attributes\Reindex;
use Support\Search\Scout\Contracts\Searchable;
use Tests\Fixtures\Tooling\Concerns\GetsFixtures;

/** @extends RuleTestCase<ReindexMustOnlyBeOnToSearchableArray> */
#[CoversClass(ReindexMustOnlyBeOnToSearchableArray::class)]
class ReindexMustOnlyBeOnToSearchableArrayTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new ReindexMustOnlyBeOnToSearchableArray;
    }

    #[Test]
    public function it_passes_when_reindex_is_on_to_searchable_array_of_a_searchable(): void
    {
        $this->analyse([$this->getFixturePath('Scout/ValidSearchableModel.php')], []);
    }

    #[Test]
    public function it_fails_when_reindex_is_on_another_method(): void
    {
        $this->analyse([$this->getFixturePath('Scout/ReindexOnOtherMethod.php')], [
            [
                class_basename(Reindex::class).' must only be on toSearchableArray() of a '.class_basename(Searchable::class).'.',
                18,
            ],
        ]);
    }

    #[Test]
    public function it_fails_when_reindex_is_on_a_non_searchable(): void
    {
        $this->analyse([$this->getFixturePath('Scout/ReindexOnNonSearchable.php')], [
            [
                class_basename(Reindex::class).' must only be on toSearchableArray() of a '.class_basename(Searchable::class).'.',
                14,
            ],
        ]);
    }
}
