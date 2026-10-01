# aryeo/eloquent-search

Laravel package providing Eloquent builder filtering and sorting, OpenSearch Scout integration, and PHPStan guardrails for Aryeo search models.

Supersedes [`aryeo/eloquent-filters`](https://github.com/AryeoHQ/eloquent-filters). See [Migrating from eloquent-filters](#migrating-from-eloquent-filters).

## Installation

```bash
composer require aryeo/eloquent-search
```

The package auto-discovers its service provider via Laravel's package discovery.

---

## Database filtering and sorting

The `Support\Search\Database` namespace provides reflection-driven filter and sort capabilities for Eloquent builder classes.

### Filters

Implement `Filterable` and apply `HasFilters` to your builder:

```php
use Illuminate\Database\Eloquent\Builder;
use Support\Search\Database\Attributes\Filter;
use Support\Search\Database\Contracts\Filterable;
use Support\Search\Database\HasFilters;

class CompanyBuilder extends Builder implements Filterable
{
    use HasFilters;

    #[Filter('status')]
    public function ofStatus(string $status): static
    {
        return $this->where('status', $status);
    }

    #[Filter('market')]
    public function inMarket(string $market): static
    {
        return $this->where('market', $market);
    }
}
```

Pass the entire request array to `filter()` — only keys that match a `#[Filter]` name are applied:

```php
Company::filter($request->all())->get();
```

### Sorting

Implement `Sortable` and apply `HasSort` to your builder:

```php
use Illuminate\Database\Eloquent\Builder;
use Support\Search\Database\Contracts\Sortable;
use Support\Search\Database\HasSort;

class CompanyBuilder extends Builder implements Sortable
{
    use HasSort;
}
```

`sort()` accepts a field name string, a `Sort` instance, or `null`. Prefix with `-` for descending order. A secondary sort on the primary key is added automatically for deterministic ordering when the sort field differs from it:

```php
Company::sort('name')->get();          // ascending
Company::sort('-name')->get();         // descending
Company::sort(null)->get();            // no sort applied
Company::sort('name', 'desc')->get();  // explicit direction
```

---

## Scout / OpenSearch integration

The `Support\Search\Scout` namespace wires models to the OpenSearch Scout driver via PHP class attributes.

### Setting up a searchable model

Apply the `InteractsWithSearchEngine` trait and implement the `Searchable` contract:

```php
use Laravel\Scout\Searchable as ScoutSearchable;
use Support\Search\Scout\Attributes\ScoutConnection;
use Support\Search\Scout\Attributes\ScoutQueue;
use Support\Search\Scout\Attributes\UseScoutBuilder;
use Support\Search\Scout\Contracts\Searchable;
use Support\Search\Scout\InteractsWithSearchEngine;

#[ScoutQueue('search')]
#[ScoutConnection('opensearch')]
#[UseScoutBuilder(CompanySearchableBuilder::class)]
class Company extends Model implements Searchable
{
    use InteractsWithSearchEngine;

    public function toSearchableArray(): array
    {
        return [
            'id'     => $this->id,
            'name'   => $this->name,
            'market' => $this->market,
        ];
    }

    public function searchableAs(): string
    {
        return 'companies';
    }
}
```

### Attributes

| Attribute | Target | Purpose |
|---|---|---|
| `#[ScoutQueue('queue-name')]` | Class | Queue used when syncing index jobs |
| `#[ScoutConnection('connection')]` | Class | Queue connection for index jobs |
| `#[UseScoutBuilder(BuilderClass::class)]` | Class | Custom Scout builder to use for searches |

All three are optional. `InteractsWithSearchEngine` reads them via reflection and caches the result per class.

---

## PHPStan rules

Twelve static analysis rules ship with the package under `Tooling\EloquentSearch\PhpStan\Rules`. Add the package ruleset to your `phpstan.neon`:

```neon
includes:
    - vendor/aryeo/eloquent-search/phpstan.package.neon
```

### Database rules

| Rule | Enforces |
|---|---|
| `FilterableMustUseHasFilters` | `Filterable` implementors must apply `HasFilters` |
| `FilterableMustOnlyBeOnBuilder` | `Filterable` may only be implemented on `Illuminate\Database\Eloquent\Builder` subclasses |
| `HasFiltersMustImplementFilterable` | Classes using `HasFilters` must implement `Filterable` |
| `HasFiltersMustOnlyBeOnBuilder` | `HasFilters` may only be used on `Builder` subclasses |
| `SortableMustUseHasSort` | `Sortable` implementors must apply `HasSort` |
| `SortableMustOnlyBeOnBuilder` | `Sortable` may only be implemented on `Builder` subclasses |
| `HasSortMustImplementSortable` | Classes using `HasSort` must implement `Sortable` |
| `HasSortMustOnlyBeOnBuilder` | `HasSort` may only be used on `Builder` subclasses |

### Scout rules

| Rule | Enforces |
|---|---|
| `SearchableMustUseInteractsWithSearchEngine` | `Searchable` contract implementors must apply `InteractsWithSearchEngine` |
| `SearchableMustNotUseScoutTraitDirectly` | Models must not use `Laravel\Scout\Searchable` directly; use `InteractsWithSearchEngine` instead |

---

## Migrating from eloquent-filters

Replace namespace imports across your codebase:

| Old (`eloquent-filters`) | New (`eloquent-search`) |
|---|---|
| `Support\Database\Eloquent\Contracts\Filterable` | `Support\Search\Database\Contracts\Filterable` |
| `Support\Database\Eloquent\Contracts\Sortable` | `Support\Search\Database\Contracts\Sortable` |
| `Support\Database\Eloquent\Contracts\Filter` | `Support\Search\Database\Contracts\Filter` |
| `Support\Database\Eloquent\Attributes\Filter` | `Support\Search\Database\Attributes\Filter` |
| `Support\Database\Eloquent\HasFilters` | `Support\Search\Database\HasFilters` |
| `Support\Database\Eloquent\HasSort` | `Support\Search\Database\HasSort` |

Update `composer.json`:

```json
{
    "repositories": [
        { "type": "vcs", "url": "https://github.com/AryeoHQ/eloquent-search" }
    ],
    "require": {
        "aryeo/eloquent-search": "dev-main"
    }
}
```

Remove the `aryeo/eloquent-filters` repository entry and require line.

---

## Testing

```bash
composer test
```

## Changelog

See [CHANGELOG](CHANGELOG.md).

## License

The MIT License (MIT). See [LICENSE](LICENSE.md).
