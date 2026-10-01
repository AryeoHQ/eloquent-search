<?php

declare(strict_types=1);

namespace Tooling\EloquentSearch\PhpStan\Rules;

use Illuminate\Database\Eloquent\Builder;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Support\Search\Database\Contracts\Sortable;
use Support\Search\Database\HasSort;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
final class HasSortMustImplementSortable extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, Builder::class)
            && $this->inherits($node, HasSort::class)
            && $this->doesNotInherit($node, Sortable::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            message: 'HasSort must implement Sortable.',
            line: $node->name?->getStartLine() ?? $node->getStartLine(),
            identifier: 'sorting.hasSort.mustImplement.sortable'
        );
    }
}
