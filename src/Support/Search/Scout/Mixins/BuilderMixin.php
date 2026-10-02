<?php

declare(strict_types=1);

namespace Support\Search\Scout\Mixins;

use Closure;
use Laravel\Scout\Builder;

/**
 * Geo and option-merging macros for Scout's Builder.
 *
 * Registered via Builder::mixin(new BuilderMixin) in SearchServiceProvider::boot().
 * PHPStan sees these through stubs/Scout/Builder.stub.php.
 *
 * post_filter runs after aggregations; use SearchRequestPayloadInterface
 * as the escape hatch when geo must constrain aggregations too.
 */
class BuilderMixin
{
    /**
     * Merge options into the builder, accumulating post_filter clauses rather
     * than replacing them. Each additional post_filter is folded into a
     * bool.must so all geo constraints apply simultaneously.
     *
     * @return Closure(array<string, mixed>): static
     */
    public function mergeOptions(): Closure
    {
        return function (array $options): static {
            foreach ($options as $key => $value) {
                if ($key === 'post_filter' && array_key_exists($key, $this->options)) {
                    $existing = $this->options[$key];
                    $musts = array_key_exists('bool', $existing)
                        && array_key_exists('must', $existing['bool'])
                        && array_is_list($existing['bool']['must'])
                        ? $existing['bool']['must']
                        : [$existing];
                    $musts[] = $value;
                    $this->options[$key] = ['bool' => ['must' => $musts]];
                } else {
                    $this->options[$key] = $value;
                }
            }

            return $this;
        };
    }

    /**
     * Post-filter results to documents whose geo_shape field intersects the
     * given GeoJSON shape (default relation: intersects for point-in-polygon).
     *
     * @return Closure(string, array<string, mixed>, string): static
     */
    public function geoShape(): Closure
    {
        return function (string $field, array $shape, string $relation = 'intersects'): static {
            return $this->mergeOptions([
                'post_filter' => [
                    'geo_shape' => [
                        $field => [
                            'shape' => $shape,
                            'relation' => $relation,
                        ],
                    ],
                ],
            ]);
        };
    }

    /**
     * Post-filter results to documents whose geo_point field falls within
     * the given distance of the supplied coordinates.
     *
     * @return Closure(string, string, float, float): static
     */
    public function geoDistance(): Closure
    {
        return function (string $field, string $distance, float $lat, float $lon): static {
            return $this->mergeOptions([
                'post_filter' => [
                    'geo_distance' => [
                        'distance' => $distance,
                        $field => ['lat' => $lat, 'lon' => $lon],
                    ],
                ],
            ]);
        };
    }
}
