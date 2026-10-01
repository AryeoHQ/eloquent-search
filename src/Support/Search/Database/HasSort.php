<?php

declare(strict_types=1);

namespace Support\Search\Database;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Support\Primitives\Direction;
use Support\Primitives\Sort;
use Support\Primitives\Text;

/**
 * @mixin Builder<Model>
 */
trait HasSort
{
    public function sort(null|string|Text|Sort $sort, null|string|Direction $direction = null): static
    {
        if ($sort === null) {
            return $this;
        }

        $sort = Sort::make($sort, $direction);
        $primaryKeyQualified = str($this->getModel()->getQualifiedKeyName());
        $requiresFallback = $primaryKeyQualified->toString() !== $this->getModel()->qualifyColumn($sort->field->toString());

        return $this->orderBy( // @phpstan-ignore staticMethod.dynamicCall, return.type
            $sort->field->toString(),
            $sort->direction->value
        )->when(
            $requiresFallback,
            fn (self $query): self => $query->orderBy( // @phpstan-ignore staticMethod.dynamicCall
                $primaryKeyQualified->toString(),
                $sort->direction->value
            )
        );
    }
}
