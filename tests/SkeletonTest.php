<?php

namespace Aryeo\Skeleton\Tests;

use Aryeo\Skeleton\Skeleton;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Skeleton::class)]
class SkeletonTest extends TestCase
{
    public function test_true_is_true()
    {
        $this->assertTrue(true);
    }
}
