<?php

declare(strict_types=1);

namespace Support\Search\Database;

use ReflectionClass;
use ReflectionMethod;
use Support\Search\Database\Attributes\Filter;

trait HasFilters
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private static array $filterMethodsCache = [];

    /**
     * @param  array<string, mixed>  $requestParams
     */
    final public function filter(array $requestParams): static
    {
        $builderClass = get_class($this);

        if (data_get(self::$filterMethodsCache, $builderClass) === null) {
            self::$filterMethodsCache[$builderClass] = $this->getFilterMethods($builderClass);
        }

        $filterMethods = self::$filterMethodsCache[$builderClass];

        foreach ($requestParams as $param => $value) {
            if (data_get($filterMethods, $param) !== null) {
                $scopeName = $filterMethods[$param];
                if (is_string($scopeName) && method_exists($this, $scopeName)) {
                    /** @var callable(mixed): static $callback */
                    $callback = [$this, $scopeName];
                    call_user_func($callback, $value);
                }
            }
        }

        return $this;
    }

    /**
     * @param  class-string  $builderClass
     * @return array<string, string>
     */
    private function getFilterMethods(string $builderClass): array
    {
        /** @var class-string $builderClass */
        $reflection = new ReflectionClass($builderClass);
        $filterMethods = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes(Filter::class);

            if (count($attributes) > 0) {
                $filterAttribute = $attributes[0]->newInstance();
                $filterMethods[$filterAttribute->name] = $method->getName();
            }
        }

        return $filterMethods;
    }
}
