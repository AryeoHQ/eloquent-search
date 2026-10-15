<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Scout\Activities;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[UseFactory(Factory::class)]
class Activity extends Model
{
    /** @use HasFactory<Factory> */
    use HasFactory;

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
