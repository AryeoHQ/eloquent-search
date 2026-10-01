<?php

namespace Aryeo\Skeleton\Tests\Commands;

use Aryeo\Skeleton\Tests\TestCase;

class SkeletonCommandTest extends TestCase
{
    public function test_skeleton_command_outputs_all_done_and_exits_successfully(): void
    {
        $this->artisan('skeleton')
            ->expectsOutput('All done')
            ->assertExitCode(0);
    }
}
