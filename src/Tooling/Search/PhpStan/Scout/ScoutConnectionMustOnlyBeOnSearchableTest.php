<?php

declare(strict_types=1);

namespace Tooling\Search\PhpStan\Scout;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Search\Scout\Attributes\ScoutConnection;
use Support\Search\Scout\Contracts\Searchable;
use Tests\Fixtures\Tooling\Concerns\GetsFixtures;

/** @extends RuleTestCase<ScoutConnectionMustOnlyBeOnSearchable> */
#[CoversClass(ScoutConnectionMustOnlyBeOnSearchable::class)]
class ScoutConnectionMustOnlyBeOnSearchableTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new ScoutConnectionMustOnlyBeOnSearchable;
    }

    #[Test]
    public function it_passes_when_scout_connection_is_on_a_searchable(): void
    {
        $this->analyse([$this->getFixturePath('Scout/ValidSearchableModel.php')], []);
    }

    #[Test]
    public function it_fails_when_scout_connection_is_not_on_a_searchable(): void
    {
        $this->analyse([$this->getFixturePath('Scout/ScoutConnectionOnNonSearchable.php')], [
            [
                class_basename(ScoutConnection::class).' must only be on '.class_basename(Searchable::class).'.',
                11,
            ],
        ]);
    }
}
