<?php

declare(strict_types=1);

namespace Tooling\Search\PhpStan\OpenSearch;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Search\OpenSearch\Adapter;
use Support\Search\OpenSearch\Adapter\Indices\IndexManager;
use Support\Search\OpenSearch\Migrations;
use Support\Search\OpenSearch\Migrations\Facades\Index;
use Tests\Fixtures\Tooling\Concerns\GetsFixtures;

/** @extends RuleTestCase<DisallowNonStrictIndexManagers> */
#[CoversClass(DisallowNonStrictIndexManagers::class)]
class DisallowNonStrictIndexManagersTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new DisallowNonStrictIndexManagers;
    }

    #[Test]
    public function it_passes_when_the_strict_index_managers_are_used(): void
    {
        $this->analyse([$this->getFixturePath('OpenSearch/UsesStrictIndexManagers.php')], []);
    }

    #[Test]
    public function it_passes_when_a_non_strict_index_manager_is_extended_or_referenced(): void
    {
        $this->analyse([$this->getFixturePath('OpenSearch/ExtendsNonStrictIndexManager.php')], []);
    }

    #[Test]
    public function it_fails_when_a_non_strict_index_manager_is_used(): void
    {
        $this->analyse([$this->getFixturePath('OpenSearch/UsesNonStrictIndexManagers.php')], [
            ['Use '.IndexManager::class.' instead, so indices stay strict.', 17],
            ['Use '.Adapter\Testing\Fakes\FakeIndexManager::class.' instead, so indices stay strict.', 18],
            ['Use '.Migrations\Testing\Fakes\FakeIndexManager::class.' instead, so indices stay strict.', 19],
            ['Use '.Index::class.' instead, so indices stay strict.', 20],
            ['Use '.Index::class.' instead, so indices stay strict.', 21],
        ]);
    }
}
