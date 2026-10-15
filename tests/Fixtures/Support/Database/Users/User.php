<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Database\Users;

use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseEloquentBuilder(Builders\Eloquent::class)]
#[UseFactory(Factory::class)]
class User extends Model
{
    /** @use HasFactory<Factory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'role',
        'status',
    ];

    protected $casts = [
        'role' => Role::class,
    ];
}
