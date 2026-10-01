<?php

declare(strict_types=1);

namespace Support\Search\Database;

use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Support\Role;
use Tests\Fixtures\Support\User;
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

        $request = new Request([
            'role' => Role::Admin,
        ]);

        $users = User::filter($request->all())
            ->get();

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

        $request = new Request([
            'role' => Role::Admin,
            'status' => 'active',
            'is_new' => true,
        ]);

        $users = User::filter($request->all())
            ->get();

        $this->assertCount(1, $users);
        $this->assertEquals($user1->id, $users->first()->id);
    }
}
