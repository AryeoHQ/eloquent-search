<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Scout\Agencies;

use Illuminate\Database\Eloquent\Factories;

/**
 * @extends Factories\Factory<Agency>
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

    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }
}
