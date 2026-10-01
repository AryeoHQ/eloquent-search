<?php

namespace Aryeo\Skeleton\Tests\Facades;

use Aryeo\Skeleton\Facades\Skeleton;
use Aryeo\Skeleton\Skeleton as SkeletonClass;
use Aryeo\Skeleton\Tests\TestCase;
use Mockery\MockInterface;

class SkeletonTest extends TestCase
{
    public function test_facade_resolves_to_skeleton_instance(): void
    {
        $resolved = Skeleton::getFacadeRoot();

        $this->assertInstanceOf(
            SkeletonClass::class,
            $resolved,
            'Skeleton facade did not resolve to an instance of the underlying Skeleton class'
        );
    }

    public function test_facade_calls_mocked_underlying_method(): void
    {
        $this->mock(
            SkeletonClass::class,
            function (MockInterface $mock) {
                $mock->expects('performAction')->once()->andReturn('mocked-result');
            }
        );

        $result = Skeleton::performAction();

        $this->assertSame('mocked-result', $result, 'Facade did not return the expected result from the mock');  // :contentReference[oaicite:13]{index=13}
    }
}
