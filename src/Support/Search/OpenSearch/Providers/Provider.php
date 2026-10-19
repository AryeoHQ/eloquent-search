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
use Support\Search\OpenSearch\Adapter\Indices\IndexManager;
use Support\Search\OpenSearch\Migrations\Console\MakeCommand;
use Support\Search\OpenSearch\Migrations\Factories\MigrationFactory;

class Provider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerClientBindings();
        $this->registerAdapterBindings();
        $this->registerMigrationBindings();
        $this->registerScoutBindings();
    }

    public function boot(): void
    {
        $this->bootMigrationCommands();
    }

    private function registerClientBindings(): void
    {
        $this->app->register(OpenSearchClientServiceProvider::class);
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

    private function bootMigrationCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        // Booted after the migrations package, so ours replaces its opensearch:make:migration.
        $this->commands(MakeCommand::class);
    }
}
