<?php

declare(strict_types=1);

namespace Tooling\Search\PhpStan\Scout;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use Support\Search\Scout\Attributes\Reindex;
use Support\Search\Scout\Contracts\Searchable;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
final class ReindexMustOnlyBeOnToSearchableArray extends Rule
{
    public function handle(Node $node, Scope $scope): void
    {
        $searchable = $this->inherits($node, Searchable::class);

        collect($node->getMethods())
            ->filter(fn (ClassMethod $method): bool => $this->hasAttribute($method, Reindex::class))
            ->reject(fn (ClassMethod $method): bool => $searchable && $method->name->toLowerString() === 'tosearchablearray')
            ->each(fn (ClassMethod $method) => $this->error(
                message: class_basename(Reindex::class).' must only be on toSearchableArray() of a '.class_basename(Searchable::class).'.',
                line: $method->name->getStartLine(),
                identifier: 'Reindex.toSearchableArray.required'
            ));
    }
}
