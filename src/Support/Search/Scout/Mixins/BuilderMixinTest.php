<?php

declare(strict_types=1);

namespace Support\Search\Scout\Mixins;

use Laravel\Scout\Builder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Support\SearchableModel;
use Tests\TestCase;

#[CoversClass(BuilderMixin::class)]
class BuilderMixinTest extends TestCase
{
    private function builder(): Builder
    {
        return new Builder(new SearchableModel, '');
    }

    // --- mergeOptions ---

    #[Test]
    public function it_sets_a_new_option(): void
    {
        $builder = $this->builder()->mergeOptions(['highlight' => ['fields' => ['*' => new \stdClass]]]);

        $this->assertArrayHasKey('highlight', $builder->options);
    }

    #[Test]
    public function it_overwrites_a_non_post_filter_option(): void
    {
        $builder = $this->builder()
            ->mergeOptions(['min_score' => 0.5])
            ->mergeOptions(['min_score' => 1.0]);

        $this->assertSame(1.0, $builder->options['min_score']);
    }

    #[Test]
    public function it_accumulates_a_second_post_filter_into_bool_must(): void
    {
        $first = ['geo_shape' => ['location' => ['shape' => ['type' => 'Point', 'coordinates' => [0.0, 0.0]], 'relation' => 'intersects']]];
        $second = ['geo_distance' => ['distance' => '10km', 'point' => ['lat' => 0.0, 'lon' => 0.0]]];

        $builder = $this->builder()
            ->mergeOptions(['post_filter' => $first])
            ->mergeOptions(['post_filter' => $second]);

        $this->assertSame(
            ['bool' => ['must' => [$first, $second]]],
            $builder->options['post_filter'],
        );
    }

    #[Test]
    public function it_accumulates_a_third_post_filter_without_extra_nesting(): void
    {
        $first = ['geo_shape' => ['a' => []]];
        $second = ['geo_shape' => ['b' => []]];
        $third = ['geo_distance' => ['distance' => '5km', 'p' => ['lat' => 0.0, 'lon' => 0.0]]];

        $builder = $this->builder()
            ->mergeOptions(['post_filter' => $first])
            ->mergeOptions(['post_filter' => $second])
            ->mergeOptions(['post_filter' => $third]);

        $this->assertSame(
            ['bool' => ['must' => [$first, $second, $third]]],
            $builder->options['post_filter'],
        );
    }

    // --- geoShape ---

    #[Test]
    public function it_sets_geo_shape_post_filter(): void
    {
        $shape = ['type' => 'Polygon', 'coordinates' => [[[-122.5, 37.7], [-122.3, 37.7], [-122.3, 37.85], [-122.5, 37.85], [-122.5, 37.7]]]];

        $builder = $this->builder()->geoShape('location', $shape);

        $this->assertSame(
            ['geo_shape' => ['location' => ['shape' => $shape, 'relation' => 'intersects']]],
            $builder->options['post_filter'],
        );
    }

    #[Test]
    public function it_respects_a_custom_relation(): void
    {
        $builder = $this->builder()->geoShape('location', ['type' => 'Point', 'coordinates' => [0.0, 0.0]], 'within');

        $this->assertSame('within', $builder->options['post_filter']['geo_shape']['location']['relation']);
    }

    #[Test]
    public function it_returns_the_builder_for_chaining_from_geo_shape(): void
    {
        $builder = $this->builder();

        $result = $builder->geoShape('location', ['type' => 'Point', 'coordinates' => [0.0, 0.0]]);

        $this->assertSame($builder, $result);
    }

    // --- geoDistance ---

    #[Test]
    public function it_sets_geo_distance_post_filter(): void
    {
        $builder = $this->builder()->geoDistance('point', '50km', 37.77, -122.42);

        $this->assertSame(
            ['geo_distance' => ['distance' => '50km', 'point' => ['lat' => 37.77, 'lon' => -122.42]]],
            $builder->options['post_filter'],
        );
    }

    #[Test]
    public function it_returns_the_builder_for_chaining_from_geo_distance(): void
    {
        $builder = $this->builder();

        $result = $builder->geoDistance('point', '1km', 0.0, 0.0);

        $this->assertSame($builder, $result);
    }

    // --- option preservation ---

    #[Test]
    public function it_preserves_unrelated_options_when_adding_geo_shape(): void
    {
        $builder = $this->builder()
            ->mergeOptions(['min_score' => 0.8])
            ->geoShape('location', ['type' => 'Point', 'coordinates' => [0.0, 0.0]]);

        $this->assertSame(0.8, $builder->options['min_score']);
        $this->assertArrayHasKey('post_filter', $builder->options);
    }

    #[Test]
    public function it_combines_geo_shape_and_geo_distance_in_bool_must(): void
    {
        $shape = ['type' => 'Polygon', 'coordinates' => [[[-1.0, -1.0], [1.0, -1.0], [1.0, 1.0], [-1.0, 1.0], [-1.0, -1.0]]]];

        $builder = $this->builder()
            ->geoShape('location', $shape)
            ->geoDistance('point', '100km', 0.0, 0.0);

        $postFilter = $builder->options['post_filter'];

        $this->assertArrayHasKey('bool', $postFilter);
        $this->assertCount(2, $postFilter['bool']['must']);
        $this->assertArrayHasKey('geo_shape', $postFilter['bool']['must'][0]);
        $this->assertArrayHasKey('geo_distance', $postFilter['bool']['must'][1]);
    }
}
