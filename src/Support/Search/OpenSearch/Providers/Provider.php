<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Providers;

use Closure;
use DirectoryTree\OpenSearchAdapter;
use DirectoryTree\OpenSearchAdapter\Indices\IndexManagerInterface;
use DirectoryTree\OpenSearchClient\OpenSearchClientServiceProvider;
use DirectoryTree\OpenSearchClient\OpenSearchManager;
use DirectoryTree\OpenSearchMigrations;
use DirectoryTree\OpenSearchMigrations\OpenSearchMigrationsServiceProvider;
use DirectoryTree\OpenSearchScoutDriver\OpenSearchScoutServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use OpenSearch\ClientFactoryInterface;
use OpenSearch\GuzzleClientFactory;
use Support\Search\OpenSearch\Adapter\Indices\IndexManager;
use Support\Search\OpenSearch\Migrations\Console\MakeCommand;
use Support\Search\OpenSearch\Migrations\Factories\MigrationFactory;

class Provider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerConfig();
        $this->registerClientBindings();
        $this->registerAdapterBindings();
        $this->registerMigrationBindings();
        $this->registerScoutBindings();
    }

    public function boot(): void
    {
        $this->bootClientConfig();
        $this->bootMigrationConfig();
        $this->bootScoutConfig();

        $this->bootMigrationCommands();
    }

    private function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../../../../config/opensearch.php', 'opensearch');
        $this->mergeConfigFrom(__DIR__.'/../../../../../config/scout.php', 'scout');
    }

    private function registerClientBindings(): void
    {
        $this->app->register(OpenSearchClientServiceProvider::class);

        $this->app->bind(ClientFactoryInterface::class, fn (): GuzzleClientFactory => new GuzzleClientFactory(
            maxRetries: config('opensearch.connection.retries'),
        ));
    }

    private function registerScoutBindings(): void
    {
        $this->app->register(OpenSearchScoutServiceProvider::class);
    }

    private function registerAdapterBindings(): void
    {
        tap(
            fn (IndexManagerInterface $manager, Application $app): IndexManager => match (true) {
                $manager instanceof IndexManager => $manager,
                default => new IndexManager($app->make(OpenSearchManager::class)->default()),
            },
            function (Closure $strict): void {
                $this->app->extend(OpenSearchAdapter\Indices\IndexManager::class, $strict);
                $this->app->extend(IndexManagerInterface::class, $strict);
            },
        );
    }

    private function registerMigrationBindings(): void
    {
        $this->app->register(OpenSearchMigrationsServiceProvider::class);

        $this->app->bind(OpenSearchMigrations\Factories\MigrationFactory::class, MigrationFactory::class);
    }

    private function bootClientConfig(): void
    {
        $connection = config('opensearch.connection');

        config([
            'opensearch-client.default' => 'default',
            'opensearch-client.connections' => ['default' => [
                'base_uri' => $connection['host'],
                'connect_timeout' => $connection['connect_timeout'],
                'timeout' => $connection['timeout'],
                'verify' => $connection['verify'],
                ...(filled($connection['username']) ? ['auth' => [$connection['username'], $connection['password']]] : []),
                ...(filled($connection['aws']['region']) ? ['auth_aws' => $connection['aws']] : []),
            ]],
        ]);

        unset(static::$publishes[OpenSearchClientServiceProvider::class]);
    }

    private function bootMigrationConfig(): void
    {
        config([
            'opensearch-migrations.storage_directory' => config('opensearch.migrations.directory'),
            'opensearch-migrations.table' => config('opensearch.migrations.table'),
            'opensearch-migrations.connection' => null,
            'opensearch-migrations.index_name_prefix' => config('scout.prefix'),
            'opensearch-migrations.alias_name_prefix' => config('scout.prefix'),
        ]);

        unset(static::$publishes[OpenSearchMigrationsServiceProvider::class]);
    }

    private function bootMigrationCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        // Booted after the migrations package, so ours replaces its opensearch:make:migration.
        $this->commands(MakeCommand::class);
    }

    private function bootScoutConfig(): void
    {
        config(['opensearch-scout.refresh_documents' => config('scout.opensearch.refresh_documents')]);

        unset(static::$publishes[OpenSearchScoutServiceProvider::class]);
    }
}
