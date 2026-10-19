<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Adapter\Indices;

use DirectoryTree\OpenSearchAdapter;
use Support\Search\OpenSearch\Adapter\Provides\EnforcesStrictMapping;

class IndexManager extends OpenSearchAdapter\Indices\IndexManager
{
    use EnforcesStrictMapping;
}
