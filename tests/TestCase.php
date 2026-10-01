<?php

namespace Aryeo\Skeleton\Tests;

use Aryeo\Skeleton\SkeletonServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench;

abstract class TestCase extends Testbench\TestCase
{
    /** @var \Illuminate\Testing\TestResponse|null */
    public static $latestResponse = null;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Aryeo\\Skeleton\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            SkeletonServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }
}
