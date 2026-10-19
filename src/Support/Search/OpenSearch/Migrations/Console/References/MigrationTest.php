<?php

declare(strict_types=1);

namespace Support\Search\OpenSearch\Migrations\Console\References;

use Illuminate\Support\Facades\Date;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(Migration::class)]
class MigrationTest extends TestCase
{
    #[Test]
    public function it_derives_the_name(): void
    {
        $this->freezeTime();

        $this->assertSame(
            Date::now()->format('Y_m_d_His').'_create_posts_index',
            (new Migration('create_posts_index'))->name->toString(),
        );
    }

    #[Test]
    public function it_derives_the_file_path(): void
    {
        $this->freezeTime();
        config(['opensearch-migrations.storage_directory' => '/migrations/']);

        $this->assertSame(
            '/migrations/'.Date::now()->format('Y_m_d_His').'_create_posts_index.php',
            (new Migration('CreatePostsIndex'))->filePath->toString(),
        );
    }
}
