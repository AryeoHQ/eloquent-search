<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Adapter\Provides;

use DirectoryTree\OpenSearchAdapter\Indices\IndexBlueprint;
use DirectoryTree\OpenSearchAdapter\Indices\Mapping;

trait EnforcesStrictMapping
{
    public function create(IndexBlueprint $index): static
    {
        return parent::create(new IndexBlueprint(
            $index->name(),
            ($index->mapping() ?? new Mapping)->dynamic('strict'),
            $index->settings(),
        ));
    }

    public function putMapping(string $index, Mapping $mapping): static
    {
        return parent::putMapping($index, $mapping->dynamic('strict'));
    }
}
