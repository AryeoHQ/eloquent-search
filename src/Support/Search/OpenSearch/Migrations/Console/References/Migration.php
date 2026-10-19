<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Migrations\Console\References;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Stringable;
use Tooling\GeneratorCommands\References\Contracts\Reference;

final class Migration implements Reference
{
    public Stringable $name;

    public function __construct(Stringable|string $name)
    {
        $this->name = str($name)->trim()->snake()->prepend(Date::now()->format('Y_m_d_His'), '_');
    }

    public Stringable $fqcn {
        get => $this->name;
    }

    public Stringable $namespace {
        get => str('');
    }

    public Stringable $baseNamespace {
        get => str('');
    }

    public null|Stringable $subNamespace {
        get => null;
    }

    public Stringable $directory {
        get => str(config('opensearch.migrations.directory'))->rtrim('/');
    }

    public Stringable $filePath {
        get => $this->directory->append('/', $this->name->toString(), '.php');
    }

    public Stringable $stubPath {
        get => str(__DIR__.'/stubs/migration.stub');
    }
}
