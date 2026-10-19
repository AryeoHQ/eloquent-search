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
use Illuminate\Support\ServiceProvider;
use OpenSearch\ClientFactoryInterface;
use OpenSearch\GuzzleClientFactory;
use Orchestra\Testbench\Attributes\WithConfig;
use Orchestra\Testbench\Attributes\WithEnv;
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

    #[Test]
    #[WithEnv('OPENSEARCH_HOST', 'host')]
    #[WithEnv('OPENSEARCH_CONNECT_TIMEOUT', '1')]
    #[WithEnv('OPENSEARCH_TIMEOUT', '2')]
    #[WithEnv('OPENSEARCH_SSL_VERIFY', 'false')]
    public function it_copies_our_connection_into_the_client_package(): void
    {
        $this->assertSame(
            ['default' => ['base_uri' => 'host', 'connect_timeout' => 1, 'timeout' => 2, 'verify' => false]],
            config('opensearch-client.connections'),
        );
    }

    #[Test]
    #[WithEnv('OPENSEARCH_USERNAME', 'username')]
    #[WithEnv('OPENSEARCH_PASSWORD', 'password')]
    #[WithEnv('OPENSEARCH_AWS_REGION', 'region')]
    public function it_adds_the_login_and_aws_signing_to_the_connection_when_they_are_set(): void
    {
        $this->assertSame(['username', 'password'], config('opensearch-client.connections.default.auth'));
        $this->assertSame(['region' => 'region', 'service' => 'es'], config('opensearch-client.connections.default.auth_aws'));
    }

    #[Test]
    public function it_leaves_the_login_and_aws_signing_out_of_the_connection_when_they_are_not_set(): void
    {
        $this->assertArrayNotHasKey('auth', config('opensearch-client.connections.default'));
        $this->assertArrayNotHasKey('auth_aws', config('opensearch-client.connections.default'));
    }

    #[Test]
    #[WithEnv('OPENSEARCH_RETRIES', '3')]
    public function it_gives_the_client_factory_our_retry_count(): void
    {
        $this->assertEquals(new GuzzleClientFactory(maxRetries: 3), $this->app->make(ClientFactoryInterface::class));
    }

    #[Test]
    #[WithEnv('OPENSEARCH_MIGRATIONS_DIRECTORY', 'directory')]
    #[WithEnv('OPENSEARCH_MIGRATIONS_TABLE', 'table')]
    #[WithEnv('SCOUT_PREFIX', 'prefix_')]
    public function it_copies_our_config_into_the_migrations_package(): void
    {
        $this->assertSame(base_path('directory'), config('opensearch-migrations.storage_directory'));
        $this->assertSame('table', config('opensearch-migrations.table'));
        $this->assertSame('prefix_', config('opensearch-migrations.index_name_prefix'));
        $this->assertSame('prefix_', config('opensearch-migrations.alias_name_prefix'));
    }

    #[Test]
    #[WithEnv('OPENSEARCH_SCOUT_REFRESH_DOCUMENTS', 'true')]
    public function it_copies_our_scout_config_into_the_scout_driver(): void
    {
        $this->assertTrue(config('opensearch-scout.refresh_documents'));
    }

    #[Test]
    #[WithConfig('opensearch-migrations.table', 'table', defer: false)]
    #[WithConfig('opensearch-scout.refresh_documents', true, defer: false)]
    #[WithEnv('OPENSEARCH_CONNECTION', 'connection')]
    #[WithEnv('OPENSEARCH_MIGRATIONS_CONNECTION', 'connection')]
    #[WithEnv('OPENSEARCH_MIGRATIONS_INDEX_NAME_PREFIX', 'index_')]
    #[WithEnv('OPENSEARCH_MIGRATIONS_ALIAS_NAME_PREFIX', 'alias_')]
    public function it_ignores_the_opensearch_packages_own_settings(): void
    {
        $this->assertSame('default', config('opensearch-client.default'));
        $this->assertSame(config('opensearch.migrations.table'), config('opensearch-migrations.table'));
        $this->assertNull(config('opensearch-migrations.connection'));
        $this->assertSame(config('scout.prefix'), config('opensearch-migrations.index_name_prefix'));
        $this->assertSame(config('scout.prefix'), config('opensearch-migrations.alias_name_prefix'));
        $this->assertSame(config('scout.opensearch.refresh_documents'), config('opensearch-scout.refresh_documents'));
    }

    #[Test]
    public function it_does_not_offer_the_opensearch_packages_config_files_to_publish(): void
    {
        $this->assertNotContains(OpenSearchClientServiceProvider::class, ServiceProvider::publishableProviders());
        $this->assertNotContains(OpenSearchMigrationsServiceProvider::class, ServiceProvider::publishableProviders());
        $this->assertNotContains(OpenSearchScoutServiceProvider::class, ServiceProvider::publishableProviders());
    }
}
