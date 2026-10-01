<?php

declare(strict_types=1);

namespace Aryeo\Skeleton\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Aryeo\Skeleton\Skeleton
 */
class Skeleton extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Aryeo\Skeleton\Skeleton::class;
    }
}
