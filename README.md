# Eloquent Search
A package providing filtering, sorting, and search for your Eloquent models.

## Installation
```bash
composer require aryeo/eloquent-search
```

## Usage

Eloquent Search is meant to be used with the `Illuminate\Database\Eloquent\Builder` classes defined on your models.

### Setting up your model

You need to tell your model to use a custom eloquent builder class:

```php
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Model;

#[UseEloquentBuilder(UserBuilder::class)]
class User extends Model
{
    //..
}
```

### Filters

#### Setting up your eloquent builder class

Implement the `Support\Search\Database\Contracts\Filterable` contract and apply the `Support\Search\Database\Provides\HasFilters` trait to your eloquent builder class:

```php
use Illuminate\Database\Eloquent\Builder;
use Support\Search\Database\Contracts\Filterable;
use Support\Search\Database\Provides\HasFilters;

class UserBuilder extends Builder implements Filterable
{
    use HasFilters;

    //..
}
```

#### Defining scopes to be used as filters

Adding the `Support\Search\Attributes\Filter` attribute over your query scopes will register them as available filters.

```php
use Support\Search\Attributes\Filter;

class UserBuilder extends Builder implements Filterable
{
    use HasFilters;

    #[Filter('role')]
    public function role(string $role): static
    {
        return $this->where('role', $role);
    }

    #[Filter('status')]
    public function ofStatus(string $status): static
    {
        return $this->where('status', $status);
    }

    #[Filter('is_new')]
    public function isNew(): static
    {
        return $this->where('created_at', '>', now()->subDays(1));
    }
}
```

#### Filtering your model

A `filter()` method is exposed on your eloquent builder so instead of writing a query using query scope like this:

```php
User::query()
    ->role('admin')
    ->status('active')
    ->get()
```

A simple array of values to filter your model can be passed into the `filter()` method. The idea is to pass the entire form request array into the filter method for easy filtering:

```php
class UserController
{
    public function index(Request $request)
    {
        return User::filter($request->all())->get();
    }
}
```

### Sort

The `HasSort` trait provides a `sort()` method on your eloquent builder that applies ordering based on a field name and direction.

#### Setting up your eloquent builder class

Implement the `Support\Search\Database\Contracts\Sortable` contract and apply the `Support\Search\Database\Provides\HasSort` trait to your eloquent builder class:

```php
use Illuminate\Database\Eloquent\Builder;
use Support\Search\Database\Contracts\Sortable;
use Support\Search\Database\Provides\HasSort;

class UserBuilder extends Builder implements Sortable
{
    use HasSort;

    //..
}
```

#### Sorting your model

The `sort()` method accepts a string field name, a `Text` instance, a `Sort` instance, or `null`. Prefix a string field name with `-` to sort in descending order:

```php
// Sort by name ascending
User::sort('name')->get()

// Sort by name descending
User::sort('-name')->get()
```

A direction can also be passed explicitly as the second parameter, either as a `Direction` enum or a string:

```php
use Support\Primitives\Direction;

User::sort('name', Direction::Desc)->get()

User::sort('name', 'desc')->get()
```

When sorting by a field other than the model's primary key, a secondary sort by the primary key is automatically applied in the same direction to ensure deterministic ordering.

You can also pass a `Sort` instance or `null` -- which results in no sorting being applied (helpful when the `sort` parameter is optional in a `Request`):

```php
use Support\Primitives\Direction;
use Support\Primitives\Sort;

User::sort(Sort::make('name', Direction::Desc))->get()

User::sort(null)->get() // no sorting applied
```

### Search

Search is built on [Laravel Scout](https://laravel.com/docs/scout) with an OpenSearch driver. The package registers Scout and the OpenSearch driver for you, so set `scout.driver` to `opensearch` and you're ready.

#### Configuration

Everything is set with env vars, and each one has a default that works for local development. There's no config file to publish.

| Variable | Default | What it does |
| --- | --- | --- |
| `OPENSEARCH_HOST` | `http://localhost:9200` | Where OpenSearch is. Scout and migrations both use it. |
| `OPENSEARCH_USERNAME` | none | The username to log in with. Leave it out if OpenSearch doesn't need a login. |
| `OPENSEARCH_PASSWORD` | none | The password to log in with. |
| `OPENSEARCH_CONNECT_TIMEOUT` | `10` | How many seconds to wait to connect. |
| `OPENSEARCH_TIMEOUT` | `30` | How many seconds to wait for a response. |
| `OPENSEARCH_SSL_VERIFY` | `true` | Whether to check OpenSearch's TLS certificate. It can also be a path to a custom certificate. |
| `OPENSEARCH_RETRIES` | `0` | How many times to retry a failed request. |
| `OPENSEARCH_AWS_REGION` | none | Signs requests for Amazon's hosted OpenSearch. See below. |
| `OPENSEARCH_AWS_SERVICE` | `es` | Use `aoss` for OpenSearch Serverless. |
| `OPENSEARCH_MIGRATIONS_DIRECTORY` | `opensearch/migrations` | Where your index migrations live, as a path from your app's root. |
| `OPENSEARCH_MIGRATIONS_TABLE` | `opensearch_migrations` | The database table that tracks which migrations have run. It's on your default database. |
| `OPENSEARCH_SCOUT_REFRESH_DOCUMENTS` | `false` | Makes indexed and deleted documents show up in searches right away. Handy in tests, but leave it off in production. |
| `SCOUT_PREFIX` | none | Scout's own prefix. It goes in front of every index and alias name, for Scout and migrations alike. Give each environment its own. |

If you use Amazon's hosted OpenSearch, set `OPENSEARCH_AWS_REGION` and install the AWS SDK with `composer require aws/aws-sdk-php`. It finds your AWS credentials the usual way, from `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY` or the server's IAM role.

#### Setting up your model

Implement the `Support\Search\Scout\Contracts\Searchable` contract and use the `Support\Search\Scout\Provides\InteractsWithSearchEngine` trait. Use this trait instead of Scout's own `Searchable` trait. It wraps Scout's trait and also reads the attributes below.

```php
use Illuminate\Database\Eloquent\Model;
use Support\Search\Scout\Contracts\Searchable;
use Support\Search\Scout\Provides\InteractsWithSearchEngine;

class User extends Model implements Searchable
{
    use InteractsWithSearchEngine;
}
```

#### Attributes

Scout normally makes you override methods to change a few per-model settings. With this trait you can use attributes instead:

```php
use Support\Search\Scout\Attributes\ScoutConnection;
use Support\Search\Scout\Attributes\ScoutQueue;
use Support\Search\Scout\Attributes\UseScoutBuilder;

#[UseScoutBuilder(UserSearchBuilder::class)]
#[ScoutQueue('search')]
#[ScoutConnection('redis')]
class User extends Model implements Searchable
{
    use InteractsWithSearchEngine;
}
```

```php
use Laravel\Scout\Builder;

/** @extends Builder<User> */
class UserSearchBuilder extends Builder
{
    //..
}
```

- `#[UseScoutBuilder]` sets the builder `User::search()` returns. It must extend `Laravel\Scout\Builder`.
- `#[ScoutQueue]` and `#[ScoutConnection]` set where the sync job goes. They only matter when `scout.queue` is on. With it off, Scout syncs right away and there's no job.

Leave an attribute off and you get Scout's default. Attributes don't carry over to subclasses, so a child model that wants them has to declare its own.

#### Reindexing when related models change

Scout only updates a model's search document when that model itself is saved. If the document also uses data from other models, add `#[Reindex]` to `toSearchableArray()` for each event that should update it.

Say each user belongs to a team, and the user's document includes the team's name. When the team changes, every user on it needs updating:

```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Support\Search\Scout\Attributes\Reindex;

class User extends Model implements Searchable
{
    use InteractsWithSearchEngine;

    #[Reindex(on: 'eloquent.saved: '.Team::class, location: 'users')]
    #[Reindex(on: TeamRenamed::class, location: 'team.users')]
    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'team' => $this->team->name,
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
```

- `on` is the event to listen for. It can be one of Eloquent's own events, like `'eloquent.saved: '.Team::class`, or your own event class, like a `TeamRenamed` event with a public `$team` property.
- `location` is the path from the event to the model that should update, in the same dot notation you'd pass to `with()`. An Eloquent event's path starts at the model it was fired for, so `users` is `Team`'s `users()` relationship. An event class's path starts at one of its properties, so `team.users` is the event's `$team`, then its `users()`. The path can go through relationships that return many models, and the whole path is eager loaded.

Each model it finds is updated the same way Scout updates a model when it's saved. So `shouldBeSearchable()`, `withoutSyncingToSearch()`, `scout.after_commit` and `scout.soft_delete` all still apply.

Then register each model in your app's service provider. The package doesn't find these on its own, because the listeners have to be set up before the first event fires:

```php
use Support\Search\Scout\Listeners\UpdateIndex;

public function boot(): void
{
    UpdateIndex::for(User::class);
}
```

#### Index migrations

Every index created through this package is strict. That covers migrations, `scout:index`, and the `IndexManager` facade. A document with a field the mapping doesn't have gets rejected instead of quietly added. A migration can't turn this off, because `dynamic` is set to `strict` after the migration builds its mapping.

For that to hold, `action.auto_create_index` has to be turned off on your OpenSearch cluster. If it's on, saving a model before its index exists makes OpenSearch create the index itself, with a mapping guessed from that one document, and that index isn't strict.

Create a migration with `php artisan opensearch:make:migration create_posts_index`. Like Laravel's database migrations, the file returns an anonymous class:

```php
use DirectoryTree\OpenSearchAdapter\Indices\Mapping;
use DirectoryTree\OpenSearchMigrations\MigrationInterface;
use Support\Search\OpenSearch\Migrations\Facades\Index;

return new class implements MigrationInterface
{
    public function up(): void
    {
        Index::create('posts', function (Mapping $mapping) {
            $mapping->keyword('id');
            $mapping->text('title');
        });
    }

    public function down(): void
    {
        Index::dropIfExists('posts');
    }
};
```

Import `Index` from this package, as the example does. `directorytree/opensearch-migrations` has an `Index` facade too, but its `fake()` doesn't make mappings strict, so tests that use it won't match what OpenSearch gets. PHPStan flags it if you import it by mistake.

Don't set `dynamic` on an object or nested field, like `$mapping->object('meta', ['dynamic' => true])`. That setting applies to everything inside the field, so it turns strict off there. Leave it out and the field stays strict like the rest of the index.

#### Testing

The `DocumentManager`, `IndexManager`, and `Index` facades each have a `fake()` method, so tests don't need a running OpenSearch. The first two are in `Support\Search\OpenSearch\Adapter\Facades`, and `Index` is in `Support\Search\OpenSearch\Migrations\Facades`. The index fakes make mappings strict just like the real ones, so your tests see what OpenSearch would get:

```php
use Support\Search\OpenSearch\Adapter\Facades\DocumentManager;

$documents = DocumentManager::fake();
```
