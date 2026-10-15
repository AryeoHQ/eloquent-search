<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Scout\Brokerages;

use Illuminate\Database\Eloquent\Factories;

/**
 * @extends Factories\Factory<Brokerage>
 */
class Factory extends Factories\Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'active' => true,
        ];
    }
}
