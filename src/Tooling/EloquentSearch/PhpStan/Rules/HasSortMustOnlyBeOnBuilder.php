<?php

declare(strict_types=1);

namespace Tooling\EloquentSearch\PhpStan\Rules;

use Illuminate\Database\Eloquent\Builder;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Support\Search\Database\HasSort;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
final class HasSortMustOnlyBeOnBuilder extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, HasSort::class)
            && $this->doesNotInherit($node, Builder::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            message: 'HasSort must only be on Builder.',
            line: $node->name->getStartLine(),
            identifier: 'sorting.hasSort.mustOnlyBeOn.builder'
        );
    }
}
