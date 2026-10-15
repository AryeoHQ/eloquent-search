<?php

declare(strict_types=1);

namespace Support\Search\Scout\Listeners;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Laravel\Scout\ModelObserver;
use Support\Search\Scout\Attributes\Reindex;

class UpdateIndex
{
    /** @var class-string<\Support\Search\Scout\Contracts\Searchable> */
    protected string $model;

    protected string $location;

    protected ModelObserver $observer {
        get => $this->observer ??= new ModelObserver;
    }

    /**
     * @param  class-string<\Support\Search\Scout\Contracts\Searchable>  $model
     */
    public function __construct(string $model, string $location)
    {
        $this->model = $model;
        $this->location = $location;
    }

    public function handle(object $event): void
    {
        match ((bool) $this->observer->afterCommit) {
            true => DB::afterCommit(fn () => $this->update($event)),
            false => $this->update($event),
        };
    }

    protected function update(object $event): void
    {
        $this->reload($this->find($event))->each(fn (Model $model) => $this->observer->saved($model));
    }

    /**
     * @return Collection<int, Model>
     */
    private function find(object $event): Collection
    {
        $path = str($this->location)->explode('.');

        $models = Collection::wrap($event instanceof Model ? $event : data_get($event, $path->shift()))
            ->map(fn (Model $model): Model => clone $model);

        if ($path->isNotEmpty()) {
            $models->load([$path->implode('.') => fn (Relation $relation): Relation => $this->includeTrashed($relation)]);
        }

        return $path
            ->reduce(
                fn (Collection $models, string $relation): Collection => new Collection($models->pluck($relation)->flatten()->filter()),
                $models
            )->filter(
                fn (Model $model): bool => $model::class === $this->model
            );
    }

    /**
     * @param  Collection<int, Model>  $models
     * @return Collection<int, Model>
     */
    private function reload(Collection $models): Collection
    {
        return new Collection($models
            ->groupBy(fn (Model $model): string => (string) $model->getConnectionName())
            ->flatMap(fn (Collection $group): Collection => $group->first()->newQuery() // @phpstan-ignore method.nonObject
                ->whereKey($group->modelKeys())
                ->when($this->keepsTrashed(), fn (Builder $query): Builder => $query->withTrashed()) // @phpstan-ignore method.notFound
                ->get()));
    }

    /**
     * @param  Relation<Model, Model, mixed>  $relation
     * @return Relation<Model, Model, mixed>
     */
    private function includeTrashed(Relation $relation): Relation
    {
        return match (true) {
            ! $this->keepsTrashed() => $relation,
            $relation instanceof MorphTo => $relation->withTrashed(),
            $relation->getRelated() instanceof $this->model => $relation->withTrashed(), // @phpstan-ignore method.notFound
            default => $relation,
        };
    }

    private function keepsTrashed(): bool
    {
        $model = new $this->model;

        // Scout keeps this check protected on its observer.
        return (fn (): bool => $this->usingSoftDeletes && $this->usesSoftDelete($model))->call($this->observer); // @phpstan-ignore argument.type
    }

    /**
     * @param  class-string<\Support\Search\Scout\Contracts\Searchable>  $model
     */
    public static function for(string $model): void
    {
        $model::reindexes()->each(
            fn (Reindex $reindex) => Event::listen($reindex->on, fn (object $event) => (new self($model, $reindex->location))->handle($event))
        );
    }
}
