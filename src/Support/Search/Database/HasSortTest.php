<?php

declare(strict_types=1);

namespace Support\Search\Database;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use Support\Primitives\Direction;
use Support\Primitives\Sort;
use Support\Primitives\Text;
use Tests\Fixtures\Support\User;
use Tests\TestCase;

#[CoversTrait(HasSort::class)]
class HasSortTest extends TestCase
{
    #[Test]
    public function it_sorts_string_ascending(): void
    {
        $member = User::factory()->member()->create();
        $admin = User::factory()->admin()->create();

        $users = User::sort('role')->get();

        $this->assertEquals($admin->getKey(), $users->first()->getKey());
        $this->assertEquals($member->getKey(), $users->last()->getKey());
    }

    #[Test]
    public function it_sorts_string_descending(): void
    {
        $member = User::factory()->member()->create();
        $admin = User::factory()->admin()->create();

        $users = User::sort('-role')->get();

        $this->assertEquals($member->getKey(), $users->first()->getKey());
        $this->assertEquals($admin->getKey(), $users->last()->getKey());
    }

    #[Test]
    public function it_does_not_apply_ordering_when_sort_is_null(): void
    {
        $query = User::sort(null);

        $this->assertEmpty($query->getQuery()->orders);
    }

    #[Test]
    public function it_sorts_text_ascending(): void
    {
        $member = User::factory()->member()->create();
        $admin = User::factory()->admin()->create();

        $users = User::sort(Text::make('role'))->get();

        $this->assertEquals($admin->getKey(), $users->first()->getKey());
        $this->assertEquals($member->getKey(), $users->last()->getKey());
    }

    #[Test]
    public function it_sorts_text_descending(): void
    {
        $member = User::factory()->member()->create();
        $admin = User::factory()->admin()->create();

        $users = User::sort(Text::make('-role'))->get();

        $this->assertEquals($member->getKey(), $users->first()->getKey());
        $this->assertEquals($admin->getKey(), $users->last()->getKey());
    }

    #[Test]
    public function it_sorts_sort_object_ascending(): void
    {
        $member = User::factory()->member()->create();
        $admin = User::factory()->admin()->create();

        $users = User::sort(Sort::make('role', Direction::Asc))->get();

        $this->assertEquals($admin->getKey(), $users->first()->getKey());
        $this->assertEquals($member->getKey(), $users->last()->getKey());
    }

    #[Test]
    public function it_sorts_sort_object_descending(): void
    {
        $member = User::factory()->member()->create();
        $admin = User::factory()->admin()->create();

        $users = User::sort(Sort::make('role', Direction::Desc))->get();

        $this->assertEquals($member->getKey(), $users->first()->getKey());
        $this->assertEquals($admin->getKey(), $users->last()->getKey());
    }

    #[Test]
    public function it_sorts_with_direction_enum_ascending(): void
    {
        $member = User::factory()->member()->create();
        $admin = User::factory()->admin()->create();

        $users = User::sort('role', Direction::Asc)->get();

        $this->assertEquals($admin->getKey(), $users->first()->getKey());
        $this->assertEquals($member->getKey(), $users->last()->getKey());
    }

    #[Test]
    public function it_sorts_with_direction_enum_descending(): void
    {
        $member = User::factory()->member()->create();
        $admin = User::factory()->admin()->create();

        $users = User::sort('role', Direction::Desc)->get();

        $this->assertEquals($member->getKey(), $users->first()->getKey());
        $this->assertEquals($admin->getKey(), $users->last()->getKey());
    }

    #[Test]
    public function it_sorts_with_string_direction_ascending(): void
    {
        $member = User::factory()->member()->create();
        $admin = User::factory()->admin()->create();

        $users = User::sort('role', 'asc')->get();

        $this->assertEquals($admin->getKey(), $users->first()->getKey());
        $this->assertEquals($member->getKey(), $users->last()->getKey());
    }

    #[Test]
    public function it_sorts_with_string_direction_descending(): void
    {
        $member = User::factory()->member()->create();
        $admin = User::factory()->admin()->create();

        $users = User::sort('role', 'desc')->get();

        $this->assertEquals($member->getKey(), $users->first()->getKey());
        $this->assertEquals($admin->getKey(), $users->last()->getKey());
    }

    #[Test]
    public function it_adds_fallback_order_when_sorting_by_non_primary_key(): void
    {
        $sort = 'role';
        $qualifiedKeyName = User::make()->getQualifiedKeyName();

        tap(
            User::sort($sort)->getQuery()->orders,
            function (array $orders) use ($qualifiedKeyName, $sort): void {
                $this->assertCount(2, $orders);
                $this->assertEquals(['column' => $sort, 'direction' => 'asc'], $orders[0]);
                $this->assertEquals(['column' => $qualifiedKeyName, 'direction' => 'asc'], $orders[1]);
            }
        );

        tap(
            User::sort("-$sort")->getQuery()->orders,
            function (array $orders) use ($qualifiedKeyName, $sort): void {
                $this->assertCount(2, $orders);
                $this->assertEquals(['column' => $sort, 'direction' => 'desc'], $orders[0]);
                $this->assertEquals(['column' => $qualifiedKeyName, 'direction' => 'desc'], $orders[1]);
            }
        );
    }

    #[Test]
    public function it_does_not_add_fallback_order_when_sorting_by_primary_key(): void
    {
        $sort = User::make()->getKeyName();
        tap(
            User::sort($sort)->getQuery()->orders,
            function (array $orders) use ($sort): void {
                $this->assertCount(1, $orders);
                $this->assertEquals(['column' => $sort, 'direction' => 'asc'], $orders[0]);
            }
        );
    }
}
