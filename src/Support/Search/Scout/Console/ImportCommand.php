<?php

declare(strict_types=1);

namespace Support\Search\Scout\Console;

use DirectoryTree\OpenSearchClient\OpenSearchManager;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Events\ModelsImported;
use Laravel\Scout\Searchable;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Backfills a model into a specific search engine without touching the other engine.
 *
 * Unlike scout:import, this command:
 *   - Targets an explicit engine via --engine (no global driver change needed)
 *   - Never clears the live index — safe to run against production traffic
 *   - For OpenSearch: disables auto-refresh during the bulk import and restores it
 *     (with a one-shot refresh) afterward, even on failure
 *
 * Migration stage usage:
 *   php artisan search:import "App\Models\Company" --engine=opensearch
 */
#[AsCommand(name: 'search:import')]
class ImportCommand extends Command
{
    protected $signature = 'search:import
        {model : Fully-qualified model class name}
        {--engine= : Scout engine driver to target (e.g. opensearch or meilisearch); defaults to scout.driver config}
        {--chunk= : Number of records per chunk (defaults to scout.chunk.searchable)}';

    protected $description = 'Backfill a model into a specific search engine without touching the other engine';

    public function handle(EngineManager $engines, Dispatcher $events): int
    {
        $modelArg = $this->argument('model');

        if (! is_string($modelArg)) {
            $this->error('Model argument is required.');

            return self::FAILURE;
        }

        try {
            $class = $this->resolveModelClass($modelArg);
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $rawEngine = $this->option('engine');
        $engineName = is_string($rawEngine) ? $rawEngine : $engines->getDefaultDriver();
        $engine = $engines->engine($engineName);
        $model = new $class;

        $rawChunk = $this->option('chunk');

        if (is_string($rawChunk)) {
            $chunkSize = (int) $rawChunk;
        } else {
            $configChunk = config('scout.chunk.searchable');
            $chunkSize = is_int($configChunk) ? $configChunk : 500;
        }

        $events->listen(ModelsImported::class, function (ModelsImported $event) use ($class): void {
            $this->line(sprintf('<comment>Imported [%s]:</comment> %d records in this batch', $class, $event->models->count()));
        });

        $refreshManager = null;
        $indexName = ScoutDispatch::searchableAs($model);

        if ($engineName === 'opensearch' && app()->bound(OpenSearchManager::class)) {
            $client = app(OpenSearchManager::class)->default();

            if ($client->indices()->exists(['index' => $indexName])) {
                $refreshManager = new IndexRefreshManager($client, $indexName);
            }
        }

        $refreshManager?->suspend();

        $keyName = ScoutDispatch::getScoutKeyName($model);

        try {
            ScoutDispatch::makeAllSearchableQuery($class)->chunkById(
                $chunkSize,
                function (Collection $models) use ($engine): void {
                    $eligible = $models->filter(static fn (Model $m) => ScoutDispatch::shouldBeSearchable($m));

                    if ($eligible->isNotEmpty()) {
                        $engine->update($eligible);
                    }

                    event(new ModelsImported($models));
                },
                $model->qualifyColumn($keyName),
                $keyName,
            );
        } finally {
            $refreshManager?->restore();
        }

        $events->forget(ModelsImported::class);

        $this->info(sprintf('All [%s] records have been imported into the [%s] engine.', $class, $engineName));

        return self::SUCCESS;
    }

    /**
     * Resolve and validate a model class, confirming it extends Eloquent Model
     * and uses Scout's Searchable trait.
     *
     * @return class-string<Model>
     *
     * @throws InvalidArgumentException
     */
    private function resolveModelClass(string $model): string
    {
        if (class_exists($model)) {
            $this->requireSearchableModel($model);

            return $model;
        }

        $namespaced = app()->getNamespace().'Models\\'.$model;

        if (class_exists($namespaced)) {
            $this->requireSearchableModel($namespaced);

            return $namespaced;
        }

        throw new InvalidArgumentException("Model [{$model}] not found.");
    }

    /**
     * Assert that the given class-string extends Eloquent Model and uses Scout's Searchable trait.
     *
     * @param  class-string  $class
     *
     * @phpstan-assert class-string<Model> $class
     *
     * @throws InvalidArgumentException
     */
    private function requireSearchableModel(string $class): void
    {
        if (is_a($class, Model::class, true)
            && in_array(Searchable::class, class_uses_recursive($class), true)) {
            return;
        }

        throw new InvalidArgumentException("Model [{$class}] must extend Eloquent Model and use Scout's Searchable trait.");
    }
}
