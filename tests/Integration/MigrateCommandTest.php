<?php

declare(strict_types=1);

namespace Tests\Integration;

use DirectoryTree\OpenSearchClient\OpenSearchClientServiceProvider;
use DirectoryTree\OpenSearchClient\OpenSearchManager;
use DirectoryTree\OpenSearchMigrations\OpenSearchMigrationsServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use OpenSearch\Client;
use Orchestra\Testbench\TestCase;
use Support\Search\Scout\Providers\SearchServiceProvider;

class MigrateCommandTest extends TestCase
{
    use RefreshDatabase;

    protected string $indexPrefix;

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            OpenSearchClientServiceProvider::class,
            OpenSearchMigrationsServiceProvider::class,
            SearchServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');

        $app['config']->set('opensearch-client', [
            'default' => 'default',
            'connections' => [
                'default' => [
                    'base_uri' => env('OPENSEARCH_HOST', 'http://127.0.0.1:9200'),
                ],
            ],
        ]);

        $app['config']->set(
            'opensearch-migrations.storage_directory',
            realpath(__DIR__.'/../../opensearch/migrations'),
        );

        $app->bind(Client::class, fn ($app) => $app->make(OpenSearchManager::class)->default());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->indexPrefix = sprintf('test_%s_', bin2hex(random_bytes(4)));
        $this->app['config']->set('opensearch-migrations.index_name_prefix', $this->indexPrefix);
    }

    protected function tearDown(): void
    {
        /** @var Client $client */
        $client = $this->app->make(Client::class);

        if ($client->indices()->exists(['index' => $this->indexPrefix.'listings'])) {
            $client->indices()->delete(['index' => $this->indexPrefix.'listings']);
        }

        parent::tearDown();
    }

    public function test_migrate_creates_listings_index(): void
    {
        $exitCode = Artisan::call('opensearch:migrate', ['--force' => true]);

        $this->assertSame(0, $exitCode);

        /** @var Client $client */
        $client = $this->app->make(Client::class);

        $this->assertTrue($client->indices()->exists(['index' => $this->indexPrefix.'listings']));
    }

    public function test_migrate_rollback_removes_listings_index(): void
    {
        Artisan::call('opensearch:migrate', ['--force' => true]);

        $exitCode = Artisan::call('opensearch:migrate:reset', ['--force' => true]);

        $this->assertSame(0, $exitCode);

        /** @var Client $client */
        $client = $this->app->make(Client::class);

        $this->assertFalse($client->indices()->exists(['index' => $this->indexPrefix.'listings']));
    }
}
