<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Migrations\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Stringable;
use Support\Search\OpenSearch\Migrations\Console\References\Migration;
use Tooling\GeneratorCommands\Concerns\GeneratorCommandCompatibility;
use Tooling\GeneratorCommands\Contracts\GeneratesFile;
use Tooling\GeneratorCommands\References\Contracts\Reference;

class MakeCommand extends GeneratorCommand implements GeneratesFile
{
    use GeneratorCommandCompatibility;

    protected $name = 'opensearch:make:migration';

    protected $description = 'Create a new migration file';

    protected $type = 'Migration';

    public Stringable $nameInput {
        get => $this->reference->name;
    }

    public Reference $reference {
        get => $this->reference ??= new Migration(str($this->argument('name')));
    }

    // Laravel's version calls itself forever when there's no namespace, and migration files have none.
    protected function qualifyClass($name)
    {
        return $name;
    }
}
