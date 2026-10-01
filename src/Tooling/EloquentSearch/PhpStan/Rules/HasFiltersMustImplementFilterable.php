<?php

declare(strict_types=1);

namespace Tooling\EloquentSearch\PhpStan\Rules;

use Illuminate\Database\Eloquent\Builder;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Support\Search\Database\Contracts\Filterable;
use Support\Search\Database\HasFilters;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
final class HasFiltersMustImplementFilterable extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, Builder::class)
            && $this->inherits($node, HasFilters::class)
            && $this->doesNotInherit($node, Filterable::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            message: 'HasFilters must implement Filterable.',
            line: $node->name?->getStartLine() ?? $node->getStartLine(),
            identifier: 'filtering.hasFilters.mustImplement.filterable'
        );
    }
}
