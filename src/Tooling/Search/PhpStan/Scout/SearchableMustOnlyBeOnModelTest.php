<?php

declare(strict_types=1);

namespace Tooling\Search\PhpStan\Scout;

use Illuminate\Database\Eloquent\Model;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Search\Scout\Contracts\Searchable;
use Tests\Fixtures\Tooling\Concerns\GetsFixtures;

/** @extends RuleTestCase<SearchableMustOnlyBeOnModel> */
#[CoversClass(SearchableMustOnlyBeOnModel::class)]
class SearchableMustOnlyBeOnModelTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new SearchableMustOnlyBeOnModel;
    }

    #[Test]
    public function it_passes_when_searchable_is_on_a_model(): void
    {
        $this->analyse([$this->getFixturePath('Scout/ValidSearchableModel.php')], []);
    }

    #[Test]
    public function it_fails_when_searchable_is_not_on_a_model(): void
    {
        $this->analyse([$this->getFixturePath('Scout/SearchableOnNonModel.php')], [
            [
                class_basename(Searchable::class).' must only be on '.class_basename(Model::class).'.',
                9,
            ],
        ]);
    }
}
