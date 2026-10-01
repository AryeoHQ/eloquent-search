<?php

/**
 * PHPStan stub for geo macros registered on Scout's Builder by BuilderMixin.
 * This file is additive — all original Builder members remain visible.
 */

namespace Laravel\Scout;

use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
class Builder
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function mergeOptions(array $options): static {}

    /**
     * Post-filter results to documents whose geo_shape field intersects the
     * given GeoJSON shape.
     *
     * @param  array<string, mixed>  $shape  GeoJSON shape definition
     */
    public function geoShape(string $field, array $shape, string $relation = 'intersects'): static {}

    /**
     * Post-filter results to documents within the given distance of a geo_point.
     */
    public function geoDistance(string $field, string $distance, float $lat, float $lon): static {}
}
