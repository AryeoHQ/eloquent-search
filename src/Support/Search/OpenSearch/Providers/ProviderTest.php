<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Providers;

use DirectoryTree\OpenSearchAdapter;
use DirectoryTree\OpenSearchAdapter\Indices\IndexManagerInterface;
use DirectoryTree\OpenSearchClient\OpenSearchClientServiceProvider;
use DirectoryTree\OpenSearchMigrations;
use DirectoryTree\OpenSearchMigrations\OpenSearchMigrationsServiceProvider;
use DirectoryTree\OpenSearchScoutDriver\OpenSearchScoutServiceProvider;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Search\OpenSearch\Adapter\Indices\IndexManager;
use Support\Search\OpenSearch\Migrations\Console\MakeCommand;
use Support\Search\OpenSearch\Migrations\Factories\MigrationFactory;
use Tests\TestCase;

#[CoversClass(Provider::class)]
class ProviderTest extends TestCase
{
    #[Test]
    public function it_registers_the_opensearch_packages(): void
    {
        $this->assertTrue($this->app->providerIsLoaded(OpenSearchClientServiceProvider::class));
        $this->assertTrue($this->app->providerIsLoaded(OpenSearchScoutServiceProvider::class));
        $this->assertTrue($this->app->providerIsLoaded(OpenSearchMigrationsServiceProvider::class));
    }

    #[Test]
    public function it_resolves_the_strict_index_manager(): void
    {
        $this->assertInstanceOf(IndexManager::class, $this->app->make(IndexManagerInterface::class));
        $this->assertInstanceOf(IndexManager::class, $this->app->make(OpenSearchAdapter\Indices\IndexManager::class));
    }

    #[Test]
    public function it_resolves_the_strict_index_manager_when_an_app_binds_its_own(): void
    {
        $this->app->singleton(IndexManagerInterface::class, fn () => self::createStub(IndexManagerInterface::class));
        $this->app->singleton(OpenSearchAdapter\Indices\IndexManager::class, fn () => self::createStub(OpenSearchAdapter\Indices\IndexManager::class));

        $this->assertInstanceOf(IndexManager::class, $this->app->make(IndexManagerInterface::class));
        $this->assertInstanceOf(IndexManager::class, $this->app->make(OpenSearchAdapter\Indices\IndexManager::class));
    }

    #[Test]
    public function it_replaces_the_make_migration_command(): void
    {
        $this->assertInstanceOf(MakeCommand::class, Artisan::all()[resolve(OpenSearchMigrations\Console\MakeCommand::class)->getName()]);
    }

    #[Test]
    public function it_resolves_the_anonymous_migration_factory(): void
    {
        $this->assertInstanceOf(MigrationFactory::class, $this->app->make(OpenSearchMigrations\Factories\MigrationFactory::class));
    }
}
