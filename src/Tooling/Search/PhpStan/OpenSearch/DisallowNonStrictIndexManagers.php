<?php

declare(strict_types=1);

namespace Tooling\Search\PhpStan\OpenSearch;

use DirectoryTree\OpenSearchAdapter;
use DirectoryTree\OpenSearchMigrations;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use Support\Search\OpenSearch\Adapter;
use Support\Search\OpenSearch\Migrations;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Node\Expr>
 */
#[NodeType(Node\Expr::class)]
final class DisallowNonStrictIndexManagers extends Rule
{
    /** @var array<class-string, class-string> */
    public const DISALLOWED = [
        OpenSearchAdapter\Indices\IndexManager::class => Adapter\Indices\IndexManager::class,
        OpenSearchAdapter\Testing\Fakes\FakeIndexManager::class => Adapter\Testing\Fakes\FakeIndexManager::class,
        OpenSearchMigrations\Facades\Index::class => Migrations\Facades\Index::class,
        OpenSearchMigrations\Testing\Fakes\FakeIndexManager::class => Migrations\Testing\Fakes\FakeIndexManager::class,
    ];

    public function shouldHandle(Node $node, Scope $scope): bool
    {
        if (! ($node instanceof New_ || $node instanceof StaticCall || $node instanceof ClassConstFetch)) {
            return false;
        }

        if ($node instanceof ClassConstFetch && $node->name instanceof Node\Identifier && $node->name->name === 'class') {
            return false;
        }

        $class = $this->findClassName($node, $scope);

        if ($class === null) {
            return false;
        }

        return collect(self::DISALLOWED)->has($class);
    }

    /**
     * @param  Node\Expr  $node
     */
    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            message: 'Use '.collect(self::DISALLOWED)->get($this->findClassName($node, $scope)).' instead, so indices stay strict.',
            line: $node->getStartLine(),
            identifier: 'OpenSearch.IndexManager.nonStrictNotAllowed'
        );
    }

    private function findClassName(Node\Expr $node, Scope $scope): null|string
    {
        // parent:: in our subclasses resolves to the DirectoryTree class.
        if (($node instanceof New_ || $node instanceof StaticCall || $node instanceof ClassConstFetch)
            && $node->class instanceof Node\Name && ! $node->class->isSpecialClassName()) {
            return $scope->resolveName($node->class);
        }

        return null;
    }
}
