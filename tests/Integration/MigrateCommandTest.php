<?php

declare(strict_types=1);

namespace Tests\Integration;

use DirectoryTree\OpenSearchClient\OpenSearchClientServiceProvider;
use DirectoryTree\OpenSearchClient\OpenSearchManager;
use DirectoryTree\OpenSearchMigrations\OpenSearchMigrationsServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use OpenSearch\Client;
use Tests\TestCase;

class MigrateCommandTest extends TestCase
{
    protected string $indexPrefix;

    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            OpenSearchClientServiceProvider::class,
            OpenSearchMigrationsServiceProvider::class,
            ...parent::getPackageProviders($app),
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('opensearch-client', [
            'default' => 'default',
            'connections' => [
                'default' => [
                    'base_uri' => getenv('OPENSEARCH_HOST') ?: 'http://127.0.0.1:9200',
                ],
            ],
        ]);

        config()->set(
            'opensearch-migrations.storage_directory',
            realpath(__DIR__.'/../../opensearch/migrations'),
        );

        $this->app->bind(Client::class, fn ($app) => $app->make(OpenSearchManager::class)->default());

        $this->indexPrefix = sprintf('test_%s_', bin2hex(random_bytes(4)));

        config()->set('opensearch-migrations.index_name_prefix', $this->indexPrefix);
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
