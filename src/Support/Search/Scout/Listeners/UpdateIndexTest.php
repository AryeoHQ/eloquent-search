<?php

declare(strict_types=1);

namespace Support\Search\Scout\Listeners;

use DirectoryTree\OpenSearchAdapter\Documents\Document;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Laravel\Scout\Jobs\MakeSearchable;
use Orchestra\Testbench\Attributes\WithConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Search\OpenSearch\Adapter\Facades\DocumentManager;
use Support\Search\OpenSearch\Adapter\Facades\IndexManager;
use Tests\Fixtures\Support\Scout\Activities\Activity;
use Tests\Fixtures\Support\Scout\Activities\Events\Logged;
use Tests\Fixtures\Support\Scout\Agencies\Agency;
use Tests\Fixtures\Support\Scout\Agencies\Events\Renamed;
use Tests\Fixtures\Support\Scout\Agents\Agent;
use Tests\Fixtures\Support\Scout\Brokerages\Brokerage;
use Tests\Fixtures\Support\Scout\Teams\Team;
use Tests\TestCase;

#[CoversClass(UpdateIndex::class)]
#[WithConfig('scout.queue', true)]
class UpdateIndexTest extends TestCase
{
    #[Test]
    public function it_registers_one_listener_per_reindex(): void
    {
        $before = collect(Event::getRawListeners())->flatten();

        UpdateIndex::for(Agency::class);
        $after = collect(Event::getRawListeners())->flatten();

        $this->assertCount(
            $before->count() + Agency::reindexes()->count(),
            $after
        );
    }

    #[Test]
    public function a_model_without_reindex_registers_nothing(): void
    {
        $before = collect(Event::getRawListeners())->flatten();

        UpdateIndex::for(Team::class);
        $after = collect(Event::getRawListeners())->flatten();

        $this->assertCount($before->count(), $after);
    }

    #[Test]
    public function an_event_class_updates_the_search_index_for_related_models(): void
    {
        Queue::fake();
        UpdateIndex::for(Agency::class);
        $agent = Model::withoutEvents(
            fn (): Agent => Agent::factory()->hasAttached(Agency::factory()->count(2))->create()
        );

        $agent->save();

        Queue::assertPushedTimes(MakeSearchable::class, $agent->agencies->count());
        $agent->agencies->each(fn (Agency $agency) => Queue::assertPushed(
            MakeSearchable::class,
            fn (MakeSearchable $job): bool => $job->models->modelKeys() === [$agency->getKey()]
        ));
    }

    #[Test]
    public function a_string_event_updates_the_search_index_once_per_model_through_a_to_many(): void
    {
        Queue::fake();
        UpdateIndex::for(Agency::class);
        [$team, $shared, $other] = Model::withoutEvents(function (): array {
            [$shared, $other] = Agency::factory()->count(2)->create();
            $team = Team::factory()
                ->has(Agent::factory()->hasAttached([$shared, $other]))
                ->has(Agent::factory()->hasAttached($shared))
                ->create();

            return [$team, $shared, $other];
        });

        Team::withoutSyncingToSearch(fn () => $team->save());

        Queue::assertPushedTimes(MakeSearchable::class, 2);
        Queue::assertPushed(MakeSearchable::class, fn (MakeSearchable $job): bool => $job->models->modelKeys() === [$shared->getKey()]);
        Queue::assertPushed(MakeSearchable::class, fn (MakeSearchable $job): bool => $job->models->modelKeys() === [$other->getKey()]);
    }

    #[Test]
    public function each_model_is_updated_in_its_own_job(): void
    {
        Queue::fake();
        UpdateIndex::for(Agency::class);
        $agent = Model::withoutEvents(
            fn (): Agent => Agent::factory()->hasAttached(Agency::factory()->count(2))->create()
        );

        $agent->save();

        // One model per job so the job can be debounced.
        Queue::assertPushedTimes(MakeSearchable::class, $agent->agencies->count());
        Queue::assertNotPushed(MakeSearchable::class, fn (MakeSearchable $job): bool => $job->models->count() !== 1);
    }

    #[Test]
    public function relationships_are_eager_loaded(): void
    {
        Queue::fake();
        Model::preventLazyLoading();
        UpdateIndex::for(Agency::class);
        $team = Model::withoutEvents(
            fn (): Team => Team::factory()->has(Agent::factory()->count(3)->hasAttached(Agency::factory()))->create()
        );

        Team::withoutSyncingToSearch(fn () => $team->save());

        Queue::assertPushedTimes(MakeSearchable::class, 3);
    }

    #[Test]
    public function a_morph_to_step_follows_each_types_own_relationship(): void
    {
        Queue::fake();
        UpdateIndex::for(Agency::class);
        [$activities, $teamAgency, $agentAgency] = Model::withoutEvents(function (): array {
            [$teamAgency, $agentAgency] = Agency::factory()->count(2)->create();
            $team = Team::factory()->for($teamAgency)->create();
            $agent = Agent::factory()->for($agentAgency, 'agency')->create();

            return [
                Activity::factory()->count(2)->sequence(
                    ['subject_type' => $team->getMorphClass(), 'subject_id' => $team->getKey()],
                    ['subject_type' => $agent->getMorphClass(), 'subject_id' => $agent->getKey()],
                )->create(),
                $teamAgency,
                $agentAgency,
            ];
        });

        event(new Logged($activities));

        Queue::assertPushed(MakeSearchable::class, fn (MakeSearchable $job): bool => $job->models->modelKeys() === [$teamAgency->getKey()]);
        Queue::assertPushed(MakeSearchable::class, fn (MakeSearchable $job): bool => $job->models->modelKeys() === [$agentAgency->getKey()]);
    }

    #[Test]
    public function only_the_registered_model_is_updated_when_a_path_ends_in_a_morph_to(): void
    {
        Queue::fake();
        UpdateIndex::for(Agency::class);
        $activity = Model::withoutEvents(function (): Activity {
            Agency::factory()->create();
            $team = Team::factory()->create();

            return Activity::factory()->create(['subject_type' => $team->getMorphClass(), 'subject_id' => $team->getKey()]);
        });

        $activity->save();

        Queue::assertNothingPushed();
    }

    #[Test]
    public function a_soft_deleted_model_is_skipped_when_scout_removes_them(): void
    {
        Queue::fake();
        UpdateIndex::for(Agency::class);
        $agency = Model::withoutEvents(fn (): Agency => Agency::factory()->trashed()->create());

        event(new Renamed($agency));

        Queue::assertNothingPushed();
    }

    #[Test]
    #[WithConfig('scout.soft_delete', true)]
    public function a_soft_deleted_model_is_updated_when_scout_keeps_them(): void
    {
        Queue::fake();
        UpdateIndex::for(Agency::class);
        $agency = Model::withoutEvents(fn (): Agency => Agency::factory()->trashed()->create());

        event(new Renamed($agency));

        Queue::assertPushed(MakeSearchable::class, fn (MakeSearchable $job): bool => $job->models->modelKeys() === [$agency->getKey()]);
    }

    #[Test]
    #[WithConfig('scout.soft_delete', true)]
    public function a_soft_deleted_model_reached_through_a_relationship_is_updated_when_scout_keeps_them(): void
    {
        Queue::fake();
        UpdateIndex::for(Agency::class);
        [$agent, $agency] = Model::withoutEvents(function (): array {
            $agency = Agency::factory()->trashed()->create();

            return [Agent::factory()->hasAttached($agency)->create(), $agency];
        });

        $agent->save();

        Queue::assertPushed(MakeSearchable::class, fn (MakeSearchable $job): bool => $job->models->modelKeys() === [$agency->getKey()]);
    }

    #[Test]
    public function a_model_on_another_connection_is_loaded_from_that_connection(): void
    {
        Queue::fake();
        config(['database.connections.tenant' => config('database.connections.testing')]);
        DB::connection('tenant')->setPdo(DB::connection()->getPdo());
        UpdateIndex::for(Agency::class);
        $agency = Model::withoutEvents(fn (): Agency => Agency::factory()->create()->setConnection('tenant'));

        event(new Renamed($agency));

        Queue::assertPushed(MakeSearchable::class, fn (MakeSearchable $job): bool => $job->models->first()?->getConnectionName() === 'tenant');
    }

    #[Test]
    public function a_subclass_loads_its_inherited_relationships(): void
    {
        $brokerage = Model::withoutEvents(fn (): Brokerage => Brokerage::factory()->hasAttached(Agent::factory())->create());

        $this->assertCount(1, $brokerage->fresh()->agents);
    }

    #[Test]
    public function already_loaded_relationships_are_reloaded(): void
    {
        Queue::fake();
        UpdateIndex::for(Agency::class);
        $team = Model::withoutEvents(function (): Team {
            $team = Team::factory()->create()->load('agents');
            Agent::factory()->for($team)->hasAttached(Agency::factory())->create();

            return $team;
        });

        Team::withoutSyncingToSearch(fn () => $team->save());

        Queue::assertPushedTimes(MakeSearchable::class, 1);
    }

    #[Test]
    public function the_triggering_models_relationships_are_left_alone(): void
    {
        Queue::fake();
        UpdateIndex::for(Agency::class);
        $team = Model::withoutEvents(fn (): Team => Team::factory()->has(Agent::factory()->hasAttached(Agency::factory()))->create());

        Team::withoutSyncingToSearch(fn () => $team->save());

        $this->assertFalse($team->relationLoaded('agents'));
    }

    #[Test]
    #[WithConfig('scout.queue', false)]
    #[WithConfig('scout.driver', 'opensearch')]
    public function related_models_are_indexed_without_relationships_loaded_while_walking(): void
    {
        IndexManager::fake();
        $documents = DocumentManager::fake();
        UpdateIndex::for(Agency::class);
        $agent = Model::withoutEvents(fn (): Agent => Agent::factory()->hasAttached(Agency::factory())->create());

        $agent->save();

        $agency = Agency::query()->sole();
        $documents->assertIndexed($agency->searchableAs(), [
            new Document((string) $agency->getScoutKey(), $agency->toSearchableArray()),
        ]);
    }

    #[Test]
    public function models_with_syncing_disabled_are_skipped(): void
    {
        Queue::fake();
        UpdateIndex::for(Agency::class);
        $agent = Model::withoutEvents(fn (): Agent => Agent::factory()->hasAttached(Agency::factory())->create());

        Agency::withoutSyncingToSearch(fn () => $agent->save());

        Queue::assertNothingPushed();
    }

    #[Test]
    #[WithConfig('scout.after_commit', true)]
    public function with_after_commit_the_index_updates_once_the_transaction_commits(): void
    {
        Queue::fake();
        UpdateIndex::for(Agency::class);
        $agent = Model::withoutEvents(fn (): Agent => Agent::factory()->hasAttached(Agency::factory())->create());

        DB::transaction(function () use ($agent): void {
            $agent->save();

            Queue::assertNothingPushed();
        });

        Queue::assertPushedTimes(MakeSearchable::class, 1);
    }

    #[Test]
    #[WithConfig('scout.after_commit', true)]
    public function with_after_commit_a_rolled_back_change_updates_nothing(): void
    {
        Queue::fake();
        UpdateIndex::for(Agency::class);
        $agent = Model::withoutEvents(fn (): Agent => Agent::factory()->hasAttached(Agency::factory())->create());

        DB::beginTransaction();
        $agent->save();
        DB::rollBack();

        Queue::assertNothingPushed();
    }

    #[Test]
    public function models_that_should_not_be_searchable_are_skipped(): void
    {
        Queue::fake();
        UpdateIndex::for(Agency::class);
        [$agent, $active] = Model::withoutEvents(function (): array {
            $active = Agency::factory()->create();
            $agent = Agent::factory()->hasAttached([$active, Agency::factory()->inactive()->create()])->create();

            return [$agent, $active];
        });

        $agent->save();

        Queue::assertPushedTimes(MakeSearchable::class, 1);
        Queue::assertPushed(MakeSearchable::class, fn (MakeSearchable $job): bool => $job->models->modelKeys() === [$active->getKey()]);
    }

    #[Test]
    public function empty_relationships_update_nothing(): void
    {
        Queue::fake();
        UpdateIndex::for(Agency::class);
        $agent = Model::withoutEvents(fn (): Agent => Agent::factory()->create());

        $agent->save();

        Queue::assertNothingPushed();
    }

    #[Test]
    public function attaching_through_a_pivot_model_updates_the_search_index_for_the_parent(): void
    {
        Queue::fake();
        UpdateIndex::for(Agency::class);
        [$agency, $agent] = Model::withoutEvents(fn (): array => [
            Agency::factory()->create(),
            Agent::factory()->create(),
        ]);

        $agency->agents()->attach($agent);

        Queue::assertPushed(
            MakeSearchable::class,
            fn (MakeSearchable $job): bool => $job->models->modelKeys() === [$agency->getKey()]
        );
    }

    #[Test]
    public function detaching_through_a_pivot_model_updates_the_search_index_for_the_parent(): void
    {
        Queue::fake();
        UpdateIndex::for(Agency::class);
        $agency = Model::withoutEvents(fn (): Agency => Agency::factory()->hasAttached(Agent::factory())->create());

        $agency->agents()->detach($agency->agents);

        Queue::assertPushed(MakeSearchable::class, fn (MakeSearchable $job): bool => $job->models->modelKeys() === [$agency->getKey()]);
    }
}
