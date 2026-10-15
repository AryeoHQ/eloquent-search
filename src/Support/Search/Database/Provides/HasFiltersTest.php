<?php

declare(strict_types=1);

namespace Support\Search\Database\Provides;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Support\Database\Users\Role;
use Tests\Fixtures\Support\Database\Users\User;
use Tests\TestCase;

#[CoversTrait(HasFilters::class)]
class HasFiltersTest extends TestCase
{
    #[Test]
    public function filter_method_proxies_to_scope_methods(): void
    {
        $user1 = User::factory()
            ->admin()
            ->active()
            ->create();
        User::factory()
            ->member()
            ->invited()
            ->create();

        $users = User::filter([
            'role' => Role::Admin,
        ])->get();

        $this->assertCount(1, $users);
        $this->assertEquals($user1->id, $users->first()->id);
    }

    #[Test]
    public function filter_method_proxies_to_scope_methods_with_multiple_filters(): void
    {
        $user1 = User::factory()
            ->admin()
            ->active()
            ->create();

        User::factory()
            ->admin()
            ->active()
            ->create([
                'created_at' => now()->subDays(2),
            ]);

        $users = User::filter([
            'role' => Role::Admin,
            'status' => 'active',
            'is_new' => true,
        ])->get();

        $this->assertCount(1, $users);
        $this->assertEquals($user1->id, $users->first()->id);
    }
}
