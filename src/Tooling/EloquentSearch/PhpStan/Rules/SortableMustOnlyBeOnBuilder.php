<?php

declare(strict_types=1);

namespace Tooling\EloquentSearch\PhpStan\Rules;

use Illuminate\Database\Eloquent\Builder;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Support\Search\Database\Contracts\Sortable;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
final class SortableMustOnlyBeOnBuilder extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, Sortable::class)
            && $this->doesNotInherit($node, Builder::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            message: 'Sortable must only be on Builder.',
            line: $node->name->getStartLine(),
            identifier: 'sorting.sortable.mustOnlyBeOn.builder'
        );
    }
}
