<?php

declare(strict_types=1);

namespace Support\Search\Scout\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Scout\Builder;
use Support\Search\Scout\Console\CompareEnginesCommand;
use Support\Search\Scout\Console\ImportCommand;
use Support\Search\Scout\Mixins\BuilderMixin;

class SearchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Builder::mixin(new BuilderMixin);

        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportCommand::class,
                CompareEnginesCommand::class,
            ]);
        }
    }
}
