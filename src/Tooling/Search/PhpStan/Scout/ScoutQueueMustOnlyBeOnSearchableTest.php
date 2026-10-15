<?php

declare(strict_types=1);

namespace Tooling\Search\PhpStan\Scout;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Search\Scout\Attributes\ScoutQueue;
use Support\Search\Scout\Contracts\Searchable;
use Tests\Fixtures\Tooling\Concerns\GetsFixtures;

/** @extends RuleTestCase<ScoutQueueMustOnlyBeOnSearchable> */
#[CoversClass(ScoutQueueMustOnlyBeOnSearchable::class)]
class ScoutQueueMustOnlyBeOnSearchableTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new ScoutQueueMustOnlyBeOnSearchable;
    }

    #[Test]
    public function it_passes_when_scout_queue_is_on_a_searchable(): void
    {
        $this->analyse([$this->getFixturePath('Scout/ValidSearchableModel.php')], []);
    }

    #[Test]
    public function it_fails_when_scout_queue_is_not_on_a_searchable(): void
    {
        $this->analyse([$this->getFixturePath('Scout/ScoutQueueOnNonSearchable.php')], [
            [
                class_basename(ScoutQueue::class).' must only be on '.class_basename(Searchable::class).'.',
                11,
            ],
        ]);
    }
}
