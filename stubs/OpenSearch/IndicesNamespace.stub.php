<?php

declare(strict_types=1);

namespace OpenSearch\Namespaces;

/**
 * Additive stub: provides return types for OpenSearch index-management calls
 * that the upstream library leaves untyped (they return from performRequest()).
 */
class IndicesNamespace
{
    /**
     * @param  array<string, mixed>  $params
     * @return array<string, array{settings: array{index: array{refresh_interval?: string}}}>
     */
    public function getSettings(array $params = []): array {}
}
