<?php

declare(strict_types=1);

namespace Tests\Integration;

use DirectoryTree\OpenSearchClient\OpenSearchClientServiceProvider;
use DirectoryTree\OpenSearchClient\OpenSearchManager;
use DirectoryTree\OpenSearchScoutDriver\Factories\SearchRequestFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Laravel\Scout\Builder;
use OpenSearch\Client;
use Support\Search\Scout\Contracts\Searchable;
use Support\Search\Scout\InteractsWithSearchEngine;
use Tests\TestCase;

class GeoMacroTest extends TestCase
{
    public const string INDEX = 'geo_macro_test';

    /** @var array<string, mixed> San Francisco point (inside the test polygon) */
    private const SF = ['lat' => 37.78, 'lon' => -122.41];

    /** @var array<string, mixed> New York City point (far outside the test polygon) */
    private const NYC = ['lat' => 40.71, 'lon' => -74.01];

    /** Square polygon enclosing the SF point above */
    private const SF_POLYGON = [
        'type' => 'Polygon',
        'coordinates' => [[
            [-122.5, 37.7],
            [-122.3, 37.7],
            [-122.3, 37.85],
            [-122.5, 37.85],
            [-122.5, 37.7],
        ]],
    ];

    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            OpenSearchClientServiceProvider::class,
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

        $this->app->bind(Client::class, fn ($app) => $app->make(OpenSearchManager::class)->default());

        $client = $this->client();

        $client->indices()->create([
            'index' => self::INDEX,
            'body' => [
                'mappings' => [
                    'properties' => [
                        'doc_id' => ['type' => 'keyword'],
                        'location' => ['type' => 'geo_shape'],
                        'point' => ['type' => 'geo_point'],
                    ],
                ],
            ],
        ]);

        $client->bulk([
            'index' => self::INDEX,
            'refresh' => true,
            'body' => [
                ['index' => ['_id' => 'sf']],
                ['doc_id' => 'sf', 'location' => ['type' => 'Point', 'coordinates' => [self::SF['lon'], self::SF['lat']]], 'point' => self::SF],
                ['index' => ['_id' => 'nyc']],
                ['doc_id' => 'nyc', 'location' => ['type' => 'Point', 'coordinates' => [self::NYC['lon'], self::NYC['lat']]], 'point' => self::NYC],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        $this->client()->indices()->delete(['index' => self::INDEX]);

        parent::tearDown();
    }

    public function test_geo_shape_matches_point_inside_polygon(): void
    {
        $hits = $this->search($this->builder()->geoShape('location', self::SF_POLYGON));

        $ids = $this->docIds($hits);

        $this->assertContains('sf', $ids);
        $this->assertNotContains('nyc', $ids);
    }

    public function test_geo_shape_nonmatch_excludes_outside_point(): void
    {
        $hits = $this->search($this->builder()->geoShape('location', self::SF_POLYGON));

        $this->assertCount(1, $hits);
    }

    public function test_geo_distance_matches_point_within_radius(): void
    {
        $hits = $this->search($this->builder()->geoDistance('point', '50km', self::SF['lat'], self::SF['lon']));

        $ids = $this->docIds($hits);

        $this->assertContains('sf', $ids);
        $this->assertNotContains('nyc', $ids);
    }

    public function test_geo_distance_nonmatch_excludes_far_point(): void
    {
        $hits = $this->search($this->builder()->geoDistance('point', '50km', self::SF['lat'], self::SF['lon']));

        $this->assertCount(1, $hits);
    }

    public function test_combined_geo_shape_and_geo_distance_both_apply(): void
    {
        // SF satisfies both; NYC satisfies neither — result should still be just SF.
        $hits = $this->search(
            $this->builder()
                ->geoShape('location', self::SF_POLYGON)
                ->geoDistance('point', '50km', self::SF['lat'], self::SF['lon']),
        );

        $this->assertCount(1, $hits);
        $this->assertSame('sf', $hits[0]['_id']);
    }

    public function test_unrelated_options_are_preserved_alongside_geo_filter(): void
    {
        $builder = $this->builder()
            ->mergeOptions(['_source' => ['doc_id']])
            ->geoShape('location', self::SF_POLYGON);

        $hits = $this->search($builder);

        $this->assertCount(1, $hits);
        $this->assertArrayHasKey('doc_id', $hits[0]['_source']);
        $this->assertArrayNotHasKey('location', $hits[0]['_source']);
    }

    // --- helpers ---

    private function client(): Client
    {
        return $this->app->make(Client::class);
    }

    private function builder(): Builder
    {
        $model = new class extends Model implements Searchable
        {
            use InteractsWithSearchEngine;

            public function toSearchableArray(): array
            {
                return [];
            }

            public function searchableAs(): string
            {
                return GeoMacroTest::INDEX;
            }
        };

        return new Builder($model, '');
    }

    /**
     * Execute a Scout Builder against real OpenSearch and return the raw hits.
     *
     * @return array<int, array<string, mixed>>
     */
    private function search(Builder $builder): array
    {
        $factory = new SearchRequestFactory;
        $request = $factory->makeFromBuilder($builder);

        $response = $this->client()->search($request->toArray());

        return $response['hits']['hits'] ?? [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $hits
     * @return array<int, string>
     */
    private function docIds(array $hits): array
    {
        return array_column($hits, '_id');
    }
}
