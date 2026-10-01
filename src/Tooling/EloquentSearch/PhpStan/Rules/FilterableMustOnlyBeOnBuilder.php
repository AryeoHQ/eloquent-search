<?php

declare(strict_types=1);

namespace Tooling\EloquentSearch\PhpStan\Rules;

use Illuminate\Database\Eloquent\Builder;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Support\Search\Database\Contracts\Filterable;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
final class FilterableMustOnlyBeOnBuilder extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, Filterable::class)
            && $this->doesNotInherit($node, Builder::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            message: 'Filterable must only be on Builder.',
            line: $node->name?->getStartLine() ?? $node->getStartLine(),
            identifier: 'filtering.filterable.mustOnlyBeOn.builder'
        );
    }
}
