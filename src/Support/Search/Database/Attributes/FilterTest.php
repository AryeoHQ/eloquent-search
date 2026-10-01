<?php

declare(strict_types=1);

namespace Support\Search\Database\Attributes;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Search\Database\Contracts;
use Tests\TestCase;

#[CoversClass(Filter::class)]
class FilterTest extends TestCase
{
    #[Test]
    public function filter_attribute_can_be_created(): void
    {
        $filter = new Filter('test');

        $this->assertSame('test', $filter->name);
    }

    #[Test]
    public function filter_attribute_is_instance_of_request_filter(): void
    {
        $filter = new Filter('test');

        $this->assertInstanceOf(Contracts\Filter::class, $filter);
    }
}
