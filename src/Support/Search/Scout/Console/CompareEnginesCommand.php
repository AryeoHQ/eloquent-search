<?php

declare(strict_types=1);

namespace Support\Search\Scout\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Laravel\Scout\Builder;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Searchable;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Runs a representative search corpus against two Scout engines and surfaces
 * ranking discrepancies for review before cutting over reads.
 *
 * Usage:
 *   php artisan search:compare-engines "App\Models\Company" \
 *       --queries="acme,zillow,real estate" \
 *       --engines=meilisearch,opensearch \
 *       --limit=10
 */
#[AsCommand(name: 'search:compare-engines')]
class CompareEnginesCommand extends Command
{
    protected $signature = 'search:compare-engines
        {model : Fully-qualified model class name}
        {--queries= : Comma-separated query strings to compare}
        {--engines=meilisearch,opensearch : Comma-separated engine driver names to compare}
        {--limit=10 : Number of results per query to compare}';

    protected $description = 'Compare search results across two engines for a representative query corpus';

    public function handle(EngineManager $engines): int
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

        $model = new $class;

        $queriesRaw = $this->option('queries');
        $queriesStr = is_string($queriesRaw) ? $queriesRaw : '';
        $queries = array_filter(
            array_map('trim', explode(',', $queriesStr)),
            static fn (string $v): bool => $v !== '',
        );

        if ($queries === []) {
            $this->error('Provide at least one query via --queries.');

            return self::FAILURE;
        }

        $enginesRaw = $this->option('engines');
        $enginesStr = is_string($enginesRaw) ? $enginesRaw : 'meilisearch,opensearch';
        $engineNames = array_filter(
            array_map('trim', explode(',', $enginesStr)),
            static fn (string $v): bool => $v !== '',
        );

        if (count($engineNames) < 2) {
            $this->error('Provide at least two engine names via --engines (e.g. --engines=meilisearch,opensearch).');

            return self::FAILURE;
        }

        $limitRaw = $this->option('limit');
        $limit = is_string($limitRaw) ? (int) $limitRaw : 10;
        $discrepancies = 0;

        $keyName = ScoutDispatch::getScoutKeyName($model);

        foreach ($queries as $query) {
            $this->line('');
            $this->line("<fg=cyan>Query:</> <comment>{$query}</comment>");

            $resultsByEngine = [];

            foreach ($engineNames as $engineName) {
                $builder = new Builder($model, $query);
                $builder->take($limit);

                $engine = $engines->engine($engineName);
                $raw = $engine->search($builder);
                $mapped = $engine->map($builder, $raw, $model);

                $resultsByEngine[$engineName] = $mapped->pluck($keyName)->all();
            }

            $discrepancies += $this->renderComparison($query, $resultsByEngine, $limit);
        }

        $this->line('');

        if ($discrepancies === 0) {
            $this->info('No discrepancies found across all queries.');
        } else {
            $this->warn(sprintf(
                '%d %s had discrepancies. Review before moving reads.',
                $discrepancies,
                abs($discrepancies) === 1 ? 'query' : 'queries',
            ));
        }

        return $discrepancies === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Render a per-query comparison table and return 1 if there are discrepancies, 0 otherwise.
     *
     * @param  array<string, array<array-key, mixed>>  $resultsByEngine
     */
    private function renderComparison(string $query, array $resultsByEngine, int $limit): int
    {
        $engineNames = array_keys($resultsByEngine);
        $keyLists = array_values($resultsByEngine);

        $maxRows = $keyLists !== [] ? max(array_map('count', $keyLists)) : 0;
        $maxRows = max($maxRows, 1);

        $rows = [];

        for ($i = 0; $i < $maxRows; $i++) {
            $row = [sprintf('#%d', $i + 1)];

            foreach ($engineNames as $idx => $name) {
                $keyRaw = $keyLists[$idx][$i] ?? null;
                $row[] = is_scalar($keyRaw) ? (string) $keyRaw : '—';
            }

            $rows[] = $row;
        }

        $headers = ['#', ...$engineNames];
        $this->table($headers, $rows);

        $first = array_slice($keyLists[0] ?? [], 0, $limit);
        $second = array_slice($keyLists[1] ?? [], 0, $limit);

        if ($first !== $second) {
            $firstEngine = $engineNames[0] ?? '';
            $secondEngine = $engineNames[1] ?? '';
            $this->warn("  ^ Result order differs between {$firstEngine} and {$secondEngine}.");

            return 1;
        }

        return 0;
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
