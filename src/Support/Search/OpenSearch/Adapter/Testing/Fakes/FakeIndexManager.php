<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Adapter\Testing\Fakes;

use DirectoryTree\OpenSearchAdapter;
use Support\Search\OpenSearch\Adapter\Provides\EnforcesStrictMapping;

class FakeIndexManager extends OpenSearchAdapter\Testing\Fakes\FakeIndexManager
{
    use EnforcesStrictMapping;
}
